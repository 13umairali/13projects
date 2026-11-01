<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;

class JobRequestAttachment extends Model
{
    protected $table = 'job_request_attachments';
    public $timestamps = false;
    protected $guarded = ['id'];


    /*===========
    |  Relations
     * =========*/



    /*===========
    |  Helpers
     * =========*/

    public function getFileSize()
    {
        $size = "";
        $path = public_path($this->file);

        if (file_exists($path)) {
            $filesize = filesize($path); // B

            $size = formatSizeUnits($filesize);
        }

        return $size;
    }
}
