package com.dahify.dahimail

import android.content.Context
import android.graphics.Bitmap
import android.graphics.Canvas
import android.graphics.Color
import android.graphics.Paint
import android.graphics.Typeface
import android.view.View
import android.widget.RemoteViews
import java.util.Locale

/** Content shared by Firebase alerts and locally posted Android alerts. Android
 * supplies the outer card, app header and accessible View/inline Reply actions. */
internal object NativeNotificationCard {
    const val VERSION = "reference-card-v3"

    fun create(context: Context, sender: String, body: String, time: String,
               categoryIcon: Int, avatar: Bitmap, compact: Boolean): RemoteViews =
        RemoteViews(context.packageName, if (compact) R.layout.notification_message_compact
            else R.layout.notification_message).apply {
            setTextViewText(R.id.notification_sender, sender)
            setTextViewText(R.id.notification_body, body)
            setTextViewText(R.id.notification_time, time)
            setImageViewResource(R.id.notification_type_icon, categoryIcon)
            setImageViewBitmap(R.id.notification_avatar, avatar)
            // Collapsed content has only 48dp on Android 12+. At large font
            // sizes, reserve that space for the sender and avatar. Expanding
            // always restores the message and time at the user's text size.
            val detailVisibility = if (compact && context.resources.configuration.fontScale > 1.3f)
                View.GONE else View.VISIBLE
            setViewVisibility(R.id.notification_body, detailVisibility)
            setViewVisibility(R.id.notification_time, detailVisibility)
        }

    // Match Avatar.colorFor and the single initial used by the in-app tile.
    fun avatar(name: String): Bitmap {
        val colors = intArrayOf(0xFF5F33E1.toInt(), 0xFF0E7C86.toInt(),
            0xFFC2410C.toInt(), 0xFFBE185D.toInt(), 0xFF1D4ED8.toInt(),
            0xFF047857.toInt(), 0xFFB45309.toInt(), 0xFF7C3AED.toInt())
        var hash = 0
        for (char in name.lowercase(Locale.ROOT)) hash = (hash * 31 + char.code) and 0x7fffffff
        val bitmap = Bitmap.createBitmap(128, 128, Bitmap.Config.ARGB_8888)
        val canvas = Canvas(bitmap)
        val paint = Paint(Paint.ANTI_ALIAS_FLAG).apply { color = colors[hash % colors.size] }
        canvas.drawCircle(64f, 64f, 64f, paint)
        // A character boundary keeps emoji and composed initials intact.
        val label = name.trim().ifBlank { "D" }
        val boundary = java.text.BreakIterator.getCharacterInstance(Locale.ROOT).apply { setText(label) }
        val initial = label.substring(0, boundary.next()).uppercase(Locale.ROOT)
        paint.color = Color.WHITE
        paint.textSize = 61f
        paint.textAlign = Paint.Align.CENTER
        paint.typeface = Typeface.DEFAULT_BOLD
        canvas.drawText(initial, 64f, 64f - (paint.ascent() + paint.descent()) / 2, paint)
        return bitmap
    }
}
