package com.dahify.dahimail

import android.app.Application
import android.content.res.Configuration
import android.graphics.Rect
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

@RunWith(RobolectricTestRunner::class)
@Config(sdk = [28, 30, 33, 35], application = Application::class)
@GraphicsMode(GraphicsMode.Mode.NATIVE)
class NativeNotificationCardTest {
    private fun descendants(view: View): List<View> = listOf(view) + if (view is ViewGroup)
        (0 until view.childCount).flatMap { descendants(view.getChildAt(it)) } else emptyList()

    @Test fun cardFitsNarrowWidthsAndLargeTextWithoutClippingImagesOrTextViews() {
        val application = RuntimeEnvironment.getApplication()
        for (scale in listOf(1f, 1.3f, 1.7f, 2f)) {
            val config = Configuration(application.resources.configuration).apply { fontScale = scale }
            val context = application.createConfigurationContext(config)
            val density = context.resources.displayMetrics.density
            for (width in listOf(240, 328)) for (compact in listOf(true, false)) {
                val view = NativeNotificationCard.create(context, "Alexandra Morgan with a long name",
                    "A long message that wraps across several lines on a narrow device. ".repeat(4),
                    "Just now", R.drawable.ic_notify_email, NativeNotificationCard.avatar("Ali"), compact)
                    .apply(context, null) as ViewGroup
                val limit = if (compact) 48 else 252
                view.measure(View.MeasureSpec.makeMeasureSpec((width * density).toInt(), View.MeasureSpec.EXACTLY),
                    View.MeasureSpec.makeMeasureSpec((limit * density).toInt(), View.MeasureSpec.AT_MOST))
                view.layout(0, 0, view.measuredWidth, view.measuredHeight)
                val case = "width=$width scale=$scale compact=$compact"
                assertEquals(case, 0, view.measuredHeightAndState and View.MEASURED_STATE_TOO_SMALL)
                for (child in descendants(view).filter { it.visibility == View.VISIBLE }) {
                    val bounds = Rect(0, 0, child.width, child.height)
                    if (child !== view) view.offsetDescendantRectToMyCoords(child, bounds)
                    assertTrue("Clipped ${child.id}: $bounds $case", bounds.left >= 0 && bounds.top >= 0 &&
                        bounds.right <= view.width && bounds.bottom <= view.height)
                    if (child is TextView && child.layout != null) {
                        assertTrue("Clipped text ${context.resources.getResourceEntryName(child.id)}: layout=${child.layout.height} height=${child.height} padding=${child.compoundPaddingTop + child.compoundPaddingBottom} $case", child.layout.height <=
                            child.height - child.compoundPaddingTop - child.compoundPaddingBottom)
                    }
                }
                val body = view.findViewById<View>(R.id.notification_body)
                val time = view.findViewById<View>(R.id.notification_time)
                assertEquals(case, if (compact && scale > 1.3f) View.GONE else View.VISIBLE, body.visibility)
                assertEquals(case, body.visibility, time.visibility)
            }
        }
    }

    @Test fun avatarUsesTheSameSenderColorAsTheInAppCard() {
        // Avatar.colorFor('Ali') selects the seventh entry in the Flutter palette.
        assertEquals(0xFFB45309.toInt(), NativeNotificationCard.avatar("Ali").getPixel(64, 10))
    }
}
