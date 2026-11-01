<?php

namespace App\Models;

use App\Models\Traits\CascadeDeletes;
use App\User;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Jorenvh\Share\ShareFacade;
use Spatie\CalendarLinks\Link;

class Job extends Model implements TranslatableContract
{
    use Translatable;
    use Sluggable;
    use CascadeDeletes;

    protected $table = 'jobs';
    protected $guarded = ['id'];
    public $timestamps = false;
    protected $dateFormat = 'U';

    public $morphsFunctions = ['productBadgeContents', 'relatedCourses', 'specificLocation', 'deleteRequest', 'visits'];

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    public static function makeSlug($title)
    {
        return SlugService::createSlug(self::class, 'slug', $title);
    }

    /* ==========
     | Translation
     * ==========*/
    public $translatedAttributes = ['title', 'description', 'summary', 'seo_description', 'important_notes'];

    public function getTitleAttribute()
    {
        return getTranslateAttributeValue($this, 'title');
    }

    public function getImportantNotesAttribute()
    {
        return getTranslateAttributeValue($this, 'important_notes');
    }

    public function getDescriptionAttribute()
    {
        return getTranslateAttributeValue($this, 'description');
    }

    public function getSummaryAttribute()
    {
        return getTranslateAttributeValue($this, 'summary');
    }

    public function getSeoDescriptionAttribute()
    {
        return getTranslateAttributeValue($this, 'seo_description');
    }

    /* ==========
     | Relations
     * ==========*/
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id', 'id');
    }

    public function category()
    {
        return $this->belongsTo(JobCategory::class, 'category_id', 'id');
    }

    public function filterOptions()
    {
        return $this->hasMany(JobSelectedFilterOption::class, 'job_id', 'id');
    }

    public function relatedCourses()
    {
        return $this->morphMany('App\Models\RelatedCourse', 'targetable');
    }

    public function prerequisites()
    {
        return $this->hasMany(Prerequisite::class, 'job_id', 'id');
    }

    public function faqs()
    {
        return $this->hasMany(Faq::class, 'job_id', 'id');
    }

    public function tags()
    {
        return $this->hasMany(Tag::class, 'job_id', 'id');
    }

    public function extraDescriptions()
    {
        return $this->hasMany(WebinarExtraDescription::class, 'job_id', 'id');
    }

    public function specificLocation()
    {
        return $this->morphOne(SpecificLocation::class, 'targetable')->select(DB::raw('*, ST_AsText(geo_center) as geo_center'));
    }

    public function deleteRequest()
    {
        return $this->morphOne(ContentDeleteRequest::class, 'targetable');
    }


    public function productBadgeContents()
    {
        return $this->morphMany(ProductBadgeContent::class, 'targetable');
    }


    public function comments()
    {
        return $this->hasMany(Comment::class, 'job_id', 'id');
    }

    public function visits()
    {
        return $this->morphMany(VisitLog::class, 'targetable');
    }

    public function employmentType()
    {
        return $this->belongsTo(JobEmploymentType::class, 'employment_type_id', 'id');
    }

    public function experienceLevel()
    {
        return $this->belongsTo(JobExperienceLevel::class, 'experience_level_id', 'id');
    }

    public function jobRequests()
    {
        return $this->hasMany(JobRequest::class, 'job_id', 'id');
    }

    /* ==========
     | Helpers
     * ==========*/
    public function getUrl()
    {
        return url("/jobs/{$this->slug}");
    }

    public function getRequestUrl()
    {
        return url("/jobs/{$this->slug}/apply");
    }

    public function getIcon($useThumbnail = true)
    {
        $icon = $this->icon;

        if ($useThumbnail and empty($icon)) {
            $icon = $this->thumbnail;
        }

        return $icon;
    }


    public function allBadges()
    {
        $badges = collect();

        $productBadgeContents = $this->productBadgeContents()
            ->whereHas('badge', function ($query) {
                $query->where('enable', true);
            })
            ->get();

        foreach ($productBadgeContents as $productBadgeContent) {
            $badge = $productBadgeContent->badge;

            if ($badge->isActive()) {
                $badges->push($productBadgeContent->badge);
            }
        }

        return $badges;
    }

    public function getShareLink($social)
    {
        $link = ShareFacade::page($this->getUrl(), $this->title)
            ->facebook()
            ->twitter()
            ->whatsapp()
            ->telegram()
            ->linkedin()
            ->getRawLinks();

        return !empty($link[$social]) ? $link[$social] : '';
    }

    public function getExpiryDate()
    {
        $date = $this->hiring_end_date;

        $jobsExpirationDaysSetting = getJobsGeneralSettings("jobs_expiration");

        if (empty($date) and !empty($jobsExpirationDaysSetting)) {
            $date = $this->created_at + ($jobsExpirationDaysSetting * 24 * 60 * 60);
        }

        return $date;
    }

    public function getWorkArrangementIconText()
    {
        $icon = "";

        switch ($this->work_arrangement) {
            case 'on_site':
                $icon = "user-tag";
                break;
            case 'remote':
                $icon = "user-remove";
                break;
            case 'hybrid':
                $icon = "user-octagon";
                break;
        }

        return $icon;
    }

}
