<?php

namespace Modules\LayoutCert\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CertificateTemplate extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'name',
        'type',
        'orientation',
        'background_image',
        'primary_color',
        'secondary_color',
        'font_family',
        'show_watermark',
        'watermark_text',
        'show_qr',
        'show_logo',
        'signatures',
        'is_active',
    ];

    protected $casts = [
        'signatures' => 'array',
        'show_watermark' => 'boolean',
        'show_qr' => 'boolean',
        'show_logo' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * The template an institute currently uses for a given certificate type. Falls back to
     * sensible defaults (via toDefault()) when nothing has been configured yet, so every
     * certificate type works out of the box without requiring setup first.
     */
    public static function forType(string $type): self
    {
        // Was unscoped -- any institute's custom template for a given
        // type could be picked up (whichever was most recently created,
        // ->latest(), regardless of which institute made it), which is
        // very likely why "custom sections/templates created for
        // Certificates are not appearing" -- an institute's own custom
        // template could be silently outranked by a different
        // institute's more-recently-created one for the same type, or
        // vice versa.
        $template = static::where('type', $type)
            ->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->where('is_active', true)
            ->latest()
            ->first();

        return $template ?? static::defaultFor($type);
    }

    public static function defaultFor(string $type): self
    {
        $instance = new static([
            'institute_id' => get_institute_id(),
            'branch_id' => get_branch_id(),
            'name' => 'Default',
            'type' => $type,
            'orientation' => 'portrait',
            'primary_color' => '#1f3a5f',
            'secondary_color' => '#2f6fed',
            'font_family' => 'DejaVu Sans',
            'show_watermark' => true,
            'show_qr' => true,
            'show_logo' => true,
            'signatures' => [],
            'is_active' => true,
        ]);
        $instance->exists = false;

        return $instance;
    }
}
