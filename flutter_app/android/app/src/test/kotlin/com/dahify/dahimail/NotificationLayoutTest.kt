package com.dahify.dahimail

import android.app.Application
import android.app.Notification
import android.app.NotificationManager
import android.graphics.Bitmap
import android.graphics.Canvas
import android.view.View
import android.view.ViewGroup
import android.widget.TextView
import org.junit.Assert.*
import org.junit.Test
import org.junit.runner.RunWith
import org.robolectric.RobolectricTestRunner
import org.robolectric.RuntimeEnvironment
import org.robolectric.annotation.Config
import org.robolectric.annotation.GraphicsMode
import java.io.File

@RunWith(RobolectricTestRunner::class)
@Config(sdk = [28, 33], application = Application::class)
@GraphicsMode(GraphicsMode.Mode.NATIVE)
class NotificationLayoutTest {
    private fun children(view: View): List<View> = listOf(view) + if (view is ViewGroup)
        (0 until view.childCount).flatMap { children(view.getChildAt(it)) } else emptyList()

    private fun post(type: String, body: String = "The new designs are ready for your review."): Notification {
        android.os.SystemClock.setCurrentTimeMillis(System.currentTimeMillis())
        val context = RuntimeEnvironment.getApplication()
        val manager = context.getSystemService(NotificationManager::class.java)
        manager.cancelAll()
        context.getSharedPreferences("native_push", 0).edit().clear().commit()
        assertTrue(NativePushReceiver.showAlert(context, mapOf(
            "notification_id" to "native-layout", "type" to type, "title" to "New update",
            "sender_name" to "Alexandra Morgan", "body" to body,
            "reply_kind" to "conversation", "reply_id" to "2", "recipient_user_id" to "1", "message_id" to "42",
            "sent_at" to "${System.currentTimeMillis() / 1000 - 120}")))
        return manager.activeNotifications.single().notification
    }

    private fun assertPresentation(notification: Notification, type: String, body: String) {
        assertNotNull("Avatar must be available to all system templates", notification.getLargeIcon())
        assertNull("No fixed custom collapsed view", notification.contentView)
        assertNull("No fixed custom expanded view", notification.bigContentView)
        assertNull("No fixed custom heads-up view", notification.headsUpContentView)
        assertTrue(notification.extras.getBoolean(Notification.EXTRA_SHOW_WHEN))
        assertTrue(notification.extras.getString(Notification.EXTRA_TEMPLATE)!!.contains("BigTextStyle"))
        assertEquals("Alexandra Morgan", notification.extras.getCharSequence(Notification.EXTRA_TITLE).toString())
        assertEquals(body, notification.extras.getCharSequence(Notification.EXTRA_BIG_TEXT).toString())
        val titles = if (type == "friend_request") listOf("View") else listOf("View", "Reply")
        assertEquals(titles, notification.actions.map { it.title.toString() })
        if (titles.size == 2) assertEquals("FlutterLocalNotificationsPluginInputResult",
            notification.actions[1].remoteInputs.single().resultKey)
    }

    @Test fun nativeTemplatesRenderChatEmailAndSocialAvatarsWithActions() {
        val context = RuntimeEnvironment.getApplication()
        for (type in listOf("chat", "email_received", "friend_request")) {
            val body = if (type == "friend_request") "Sent you a friend request" else "The new designs are ready for your review."
            val notification = post(type, body)
            assertPresentation(notification, type, body)
            val framework = Notification.Builder.recoverBuilder(context, notification)
            for ((name, remote) in listOf("expanded" to framework.createBigContentView(),
                "heads-up" to framework.createHeadsUpContentView(), "collapsed" to framework.createContentView())) {
                val root = remote.apply(context, null)
                val density = context.resources.displayMetrics.density
                val width = (360 * density).toInt()
                root.measure(View.MeasureSpec.makeMeasureSpec(width, View.MeasureSpec.EXACTLY),
                    View.MeasureSpec.makeMeasureSpec((500 * density).toInt(), View.MeasureSpec.AT_MOST))
                root.layout(0, 0, width, root.measuredHeight)
                assertEquals("No second logo inside the system content", 0,
                    children(root).count { it.id == R.id.notification_brand })
                val text = children(root).filterIsInstance<TextView>().filter { it.visibility == View.VISIBLE }.map { it.text.toString() }
                assertTrue("Missing sender in $type $name: $text", text.any { it.contains("Alexandra Morgan") })
                if (name == "collapsed") {
                    val avatarId = context.resources.getIdentifier("right_icon", "id", "android")
                    val avatar = root.findViewById<android.widget.ImageView>(avatarId)
                    assertNotNull("Compact avatar missing for $type", avatar)
                    assertEquals(View.VISIBLE, avatar.visibility)
                    assertNotNull(avatar.drawable)
                    assertTrue(avatar.width > 0 && avatar.height > 0)
                }
                if (name != "collapsed") {
                    for (title in notification.actions.map { it.title.toString() })
                        assertTrue("Missing $title in $type $name: $text", text.contains(title))
                }
                System.getenv("NOTIFICATION_PREVIEW_DIR")?.let { directory ->
                    File(directory).mkdirs()
                    val bitmap = Bitmap.createBitmap(width, root.height, Bitmap.Config.ARGB_8888)
                    Canvas(bitmap).apply { drawColor(android.graphics.Color.WHITE); root.draw(this) }
                    File(directory, "android-$type-${android.os.Build.VERSION.SDK_INT}-$name.png").outputStream()
                        .use { bitmap.compress(Bitmap.CompressFormat.PNG, 100, it) }
                    bitmap.recycle()
                }
            }
        }
    }

    private fun assertInside(root: ViewGroup, child: View, label: String) {
        val rect = android.graphics.Rect(0, 0, child.width, child.height)
        root.offsetDescendantRectToMyCoords(child, rect)
        assertTrue("$label starts outside the card: $rect", rect.left >= 0 && rect.top >= 0)
        assertTrue("$label clipped horizontally: $rect", rect.right <= root.width)
        assertTrue("$label clipped vertically: $rect", rect.bottom <= root.height)
    }

    @Test fun longMessagesKeepTheirFullTextAndNativeActionsAtNarrowWidthsAndLargeFonts() {
        val context = RuntimeEnvironment.getApplication()
        val original = android.content.res.Configuration(context.resources.configuration)
        val body = (1..12).joinToString("\n") { "Message line $it for review" }
        try {
            for (scale in listOf(1f, 1.7f, 2f)) {
                @Suppress("DEPRECATION")
                context.resources.updateConfiguration(android.content.res.Configuration(original).apply { fontScale = scale },
                    context.resources.displayMetrics)
                for (type in listOf("chat", "email_received")) {
                    val notification = post(type, body)
                    assertPresentation(notification, type, body)
                    val builder = Notification.Builder.recoverBuilder(context, notification)
                    for (widthDp in listOf(240, 280, 360)) {
                        for ((name, remote) in listOf("expanded" to builder.createBigContentView(), "heads-up" to builder.createHeadsUpContentView())) {
                            val root = remote.apply(context, null) as ViewGroup
                            val density = context.resources.displayMetrics.density
                            val width = (widthDp * density).toInt()
                            root.measure(View.MeasureSpec.makeMeasureSpec(width, View.MeasureSpec.EXACTLY),
                                View.MeasureSpec.makeMeasureSpec((500 * density).toInt(), View.MeasureSpec.AT_MOST))
                            root.layout(0, 0, width, root.measuredHeight)
                            val actions = children(root).filterIsInstance<TextView>().filter {
                                it.visibility == View.VISIBLE && it.text.toString() in listOf("View", "Reply") }
                            assertEquals(2, actions.size)
                            for (action in actions) assertInside(root, action, "$type $name width=$widthDp scale=$scale ${action.text}")
                        }
                    }
                }
            }
        } finally {
            @Suppress("DEPRECATION")
            context.resources.updateConfiguration(original, context.resources.displayMetrics)
        }
    }

    @Test fun senderAvatarUsesTheNotificationCenterPalette() {
        assertEquals(0xFFB45309.toInt(), NativePushReceiver.avatarColor("Ali"))
        assertEquals(NativePushReceiver.avatarColor("Ali"), NativePushReceiver.avatarColor("ALI"))
        val bitmap = NativePushReceiver.avatarPlaceholder("Ali")
        assertEquals(NativePushReceiver.avatarColor("Ali"), bitmap.getPixel(64, 10))
        assertEquals(0, bitmap.getPixel(0, 0))
    }

    @Test fun everyManufacturerReceivesTheSameNativeStyleAndAvatar() {
        val manufacturer = android.os.Build.MANUFACTURER
        val brand = android.os.Build.BRAND
        try {
            for (maker in listOf("Xiaomi", "Redmi", "POCO", "Samsung", "Google", "OnePlus", "OPPO", "vivo", "unknown")) {
                org.robolectric.shadows.ShadowBuild.setManufacturer(maker)
                org.robolectric.shadows.ShadowBuild.setBrand(maker)
                for (type in listOf("chat", "email_received")) {
                    val notification = post(type)
                    assertPresentation(notification, type, "The new designs are ready for your review.")
                }
            }
        } finally {
            org.robolectric.shadows.ShadowBuild.setManufacturer(manufacturer)
            org.robolectric.shadows.ShadowBuild.setBrand(brand)
        }
    }

    @Test @Config(sdk = [24]) @GraphicsMode(GraphicsMode.Mode.LEGACY)
    fun minimumConfiguredAndroidRetainsTheMessageAvatarAndNativeReply() {
        val notification = post("chat", "First line\nSecond line")
        assertEquals("First line\nSecond line", notification.extras.getCharSequence(Notification.EXTRA_BIG_TEXT).toString())
        assertNotNull(notification.getLargeIcon())
        assertEquals(listOf("View", "Reply"), notification.actions.map { it.title.toString() })
        assertEquals("FlutterLocalNotificationsPluginInputResult", notification.actions[1].remoteInputs.single().resultKey)
    }
}
