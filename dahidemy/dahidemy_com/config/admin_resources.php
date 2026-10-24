<?php

/*
|--------------------------------------------------------------------------
| Admin CMS resources
|--------------------------------------------------------------------------
|
| A declarative registry of the "website content" modules managed from the
| SaaS panel. Each entry maps 1:1 to a backend apiResource (e.g. saas-banners)
| and fully describes its table columns + form fields, so a single generic
| controller + two Blade views drive CRUD for every one of them.
|
| Field types: text | textarea | email | url | number | select | image
| Field keys : name, label, type, rules[], options{value:label}, help, accept,
|              list (show as a table column), primary (bold first column)
|
| Adding a new module (Blog, etc.) is just another entry here — no new
| controller, route or view required.
|
| Field names and rules below mirror the BACKEND schema + FormRequest for each
| module (Modules/Authentication/{Http/Requests,database/migrations}). They are
| not guesses: a name that does not exist as a column is silently discarded by
| the API, and a missing required column makes every save fail. Check the
| migration before adding a field here.
|
| `storage` is the backend's public-disk directory for that module's uploads —
| it is what turns a stored filename into a URL. See App\Support\ImageUrl.
|
*/

// Backend rule for every SaaS module's status column: nullable|integer|in:0,1.
$status = [
    'name' => 'status',
    'label' => 'Status',
    'type' => 'select',
    'options' => ['1' => 'Active', '0' => 'Inactive'],
    'default' => '1',
    'rules' => ['required', 'in:0,1'],
    'list' => true,
];

// The backend validates `icon` as an image upload (nullable|image), not a name.
$iconImage = [
    'name' => 'icon',
    'label' => 'Icon',
    'type' => 'image',
    'rules' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
    'list' => true,
    'help' => 'Square image works best. Rows without one fall back to a built-in icon.',
];

// $kind picks the stand-in shown when a record has no image: image | avatar | logo.
$image = fn (string $label = 'Image', string $name = 'image', string $kind = 'image') => [
    'name' => $name,
    'label' => $label,
    'type' => 'image',
    'kind' => $kind,
    'rules' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
    'list' => true,
];

return [

    'packages' => [
        'endpoint' => 'plans',
        'singular' => 'Package',
        'plural' => 'Packages',
        'group' => 'Management',
        'description' => 'Subscription plans institutes sign up to — pricing, limits and availability. Live plans drive the public pricing page.',
        'icon' => 'M21 16V8a2 2 0 00-1-1.7l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.7l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z',
        'fields' => [
            ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'rules' => ['required', 'string', 'max:255'], 'list' => true, 'primary' => true],
            ['name' => 'price', 'label' => 'Price', 'type' => 'number', 'step' => '0.01', 'min' => '0', 'rules' => ['required', 'numeric', 'min:0', 'max:999999.99'], 'list' => true, 'help' => 'Charge per billing period. Use 0 for a free plan.'],
            ['name' => 'duration_days', 'label' => 'Duration (days)', 'type' => 'number', 'min' => '1', 'rules' => ['nullable', 'integer', 'min:1'], 'list' => true, 'help' => 'Billing period length. Leave blank for lifetime / custom.'],
            ['name' => 'trial_days', 'label' => 'Trial days', 'type' => 'number', 'min' => '0', 'rules' => ['nullable', 'integer', 'min:0'], 'list' => true, 'help' => 'Free days a new sign-up gets on this plan before it is billed. Separate from Duration above - each plan can have its own trial length. Leave blank or 0 for no trial.'],
            ['name' => 'student_limit', 'label' => 'Student limit', 'type' => 'number', 'min' => '0', 'rules' => ['required', 'integer', 'min:0'], 'list' => true, 'help' => 'Maximum students. Use 0 for unlimited.'],
            ['name' => 'branch_limit', 'label' => 'Branch limit', 'type' => 'number', 'min' => '0', 'rules' => ['required', 'integer', 'min:0'], 'list' => true, 'help' => 'Maximum branches. Use 0 for unlimited.'],
            ['name' => 'is_free', 'label' => 'Free plan', 'type' => 'select', 'options' => ['0' => 'No', '1' => 'Yes'], 'default' => '0', 'rules' => ['required', 'in:0,1'], 'list' => true],
            ['name' => 'is_custom', 'label' => 'Custom / enterprise', 'type' => 'select', 'options' => ['0' => 'No', '1' => 'Yes'], 'default' => '0', 'rules' => ['required', 'in:0,1'], 'list' => false, 'help' => 'Custom plans are quoted individually rather than self-serve.'],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['1' => 'Active', '2' => 'Inactive'], 'default' => '1', 'rules' => ['required', 'in:1,2'], 'list' => true],
            ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'rules' => ['nullable', 'string'], 'list' => false, 'help' => 'Short summary shown on the pricing card.'],
        ],
    ],

    'banners' => [
        'endpoint' => 'saas-banners',
        'storage' => 'saas_banners',
        'singular' => 'Banner',
        'plural' => 'Banners',
        'group' => 'Website content',
        'description' => 'Hero headline shown at the top of the marketing site.',
        'icon' => 'M3 5h18v14H3zM3 15l4-4 4 4M14 12l2-2 5 5M16.5 9.5a1 1 0 100-2 1 1 0 000 2z',
        'fields' => [
            ['name' => 'title', 'label' => 'Headline', 'type' => 'text', 'rules' => ['required', 'string', 'max:255'], 'list' => true, 'primary' => true],
            ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'rules' => ['nullable', 'string'], 'list' => true, 'help' => 'Supporting sentence under the headline.'],
            $image('Banner image'),
            ['name' => 'button_name', 'label' => 'Button label', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:100'], 'list' => false],
            ['name' => 'button_link', 'label' => 'Button link', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:500'], 'list' => false, 'help' => 'Absolute URL or a site path such as /register.'],
            ['name' => 'button_two_name', 'label' => 'Second button label', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:100'], 'list' => false],
            ['name' => 'button_two_link', 'label' => 'Second button link', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:500'], 'list' => false],
            $status,
        ],
    ],

    'cores' => [
        'endpoint' => 'saas-cores',
        'storage' => 'saas_cores',
        'singular' => 'Feature',
        'plural' => 'Core features',
        'group' => 'Website content',
        'description' => 'The core feature cards in the "Everything you need" grid.',
        'icon' => 'M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h6v6h-6z',
        'fields' => [
            ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'rules' => ['required', 'string', 'max:255'], 'list' => true, 'primary' => true],
            ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'rules' => ['nullable', 'string'], 'list' => true],
            $iconImage,
            $status,
        ],
    ],

    'solutions' => [
        'endpoint' => 'saas-solutions',
        'storage' => 'saas_solutions',
        'singular' => 'Solution',
        'plural' => 'Solutions',
        'group' => 'Website content',
        'description' => 'The "Solutions that fit how you work" cards.',
        'icon' => 'M12 2l9 5v10l-9 5-9-5V7zM3 7l9 5 9-5M12 12v10',
        'fields' => [
            ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'rules' => ['required', 'string', 'max:255'], 'list' => true, 'primary' => true],
            ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'rules' => ['nullable', 'string'], 'list' => true],
            $iconImage,
            $status,
        ],
    ],

    'benefits' => [
        'endpoint' => 'saas-benefits',
        'storage' => 'saas_benefits',
        'singular' => 'Benefit',
        'plural' => 'Benefits',
        'group' => 'Website content',
        'description' => 'Value props listed in the "Less admin, more teaching" section.',
        'icon' => 'M12 21s-7-4.5-9.5-8.5C1 9.5 3 5.5 6.5 5.5 9 5.5 12 8.5 12 8.5s3-3 5.5-3C21 5.5 23 9.5 21.5 12.5 19 16.5 12 21 12 21z',
        'fields' => [
            // `heading` is NOT NULL on s_a_a_s_benefits — a save without it fails.
            ['name' => 'heading', 'label' => 'Section heading', 'type' => 'text', 'rules' => ['required', 'string', 'max:255'], 'list' => true, 'primary' => true, 'help' => 'Heading above the whole benefits section.'],
            ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'rules' => ['required', 'string', 'max:255'], 'list' => true],
            ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'rules' => ['nullable', 'string'], 'list' => false],
            $image('Image'),
            ['name' => 'para_title_one', 'label' => 'Bullet one', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:100'], 'list' => false],
            ['name' => 'para_title_two', 'label' => 'Bullet two', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:100'], 'list' => false],
            ['name' => 'para_title_three', 'label' => 'Bullet three', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:100'], 'list' => false],
            ['name' => 'para_title_four', 'label' => 'Bullet four', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:100'], 'list' => false],
            $status,
        ],
    ],

    'feedback' => [
        'endpoint' => 'saas-feedback',
        'storage' => 'saas_feedbacks',
        'singular' => 'Testimonial',
        'plural' => 'Testimonials',
        'group' => 'Website content',
        'description' => 'Customer testimonials in the "Trusted worldwide" section.',
        'icon' => 'M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8z',
        'fields' => [
            ['name' => 'name', 'label' => 'Person name', 'type' => 'text', 'rules' => ['required', 'string', 'max:255'], 'list' => true, 'primary' => true],
            ['name' => 'description', 'label' => 'Testimonial', 'type' => 'textarea', 'rules' => ['required', 'string'], 'list' => false],
            $image('Thumbnail', 'thumbnail_image', 'avatar'),
            ['name' => 'video_url', 'label' => 'Video URL', 'type' => 'url', 'rules' => ['nullable', 'string', 'max:500'], 'list' => false, 'help' => 'Optional YouTube / Vimeo link for a video testimonial.'],
            ['name' => 'note', 'label' => 'Note', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:1000'], 'list' => true, 'help' => 'Shown under the name — e.g. Principal, Sunrise Academy.'],
            // NB: the column really is spelled `ratting` on s_a_a_s_feedback.
            ['name' => 'ratting', 'label' => 'Rating', 'type' => 'select', 'rules' => ['nullable', 'integer', 'between:1,5'], 'options' => ['5' => '5 — Excellent', '4' => '4 — Great', '3' => '3 — Good', '2' => '2 — Fair', '1' => '1 — Poor'], 'default' => '5', 'list' => true],
            $status,
        ],
    ],

    'faqs' => [
        'endpoint' => 'saas-faqs',
        'singular' => 'FAQ',
        'plural' => 'FAQs',
        'group' => 'Website content',
        'description' => 'Frequently asked questions shown on the FAQ page.',
        'icon' => 'M12 22a10 10 0 100-20 10 10 0 000 20zM9.1 9a3 3 0 015.8 1c0 2-3 3-3 3M12 17h.01',
        'fields' => [
            ['name' => 'question', 'label' => 'Question', 'type' => 'text', 'rules' => ['required', 'string', 'max:255'], 'list' => true, 'primary' => true],
            ['name' => 'answer', 'label' => 'Answer', 'type' => 'textarea', 'rules' => ['required', 'string', 'max:1000'], 'list' => true],
        ],
    ],

    'panels' => [
        'endpoint' => 'saas-panels',
        'storage' => 'saas_panels',
        'singular' => 'Panel',
        'plural' => 'Role panels',
        'group' => 'Website content',
        'description' => 'Role showcases (admin, teacher, parent, student) on the site.',
        'icon' => 'M3 3h18v18H3zM3 9h18M9 21V9',
        'fields' => [
            ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'rules' => ['required', 'string', 'max:255'], 'list' => true, 'primary' => true],
            ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'rules' => ['nullable', 'string'], 'list' => true],
            $iconImage,
            ['name' => 'button_name', 'label' => 'Button label', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:100'], 'list' => false],
            ['name' => 'button_link', 'label' => 'Button link', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:255'], 'list' => false, 'help' => 'Absolute URL or a site path such as /register.'],
            $status,
        ],
    ],

    'institute-types' => [
        'endpoint' => 'institute-types',
        'storage' => 'institute_types',
        'singular' => 'Institute type',
        'plural' => 'Institute types',
        'group' => 'Website content',
        'description' => 'Categories of institution shown in the sign-up flow.',
        'icon' => 'M20.6 13.4L13.4 20.6a2 2 0 01-2.8 0l-7.2-7.2A2 2 0 013 12V4a1 1 0 011-1h8a2 2 0 011.4.6l7.2 7.2a2 2 0 010 2.6zM7.5 7.5h.01',
        'fields' => [
            [
                'name' => 'title',
                'label' => 'Title',
                'type' => 'text',
                'rules' => ['required', 'string', 'max:255'],
                'list' => true,
                'primary' => true,
            ],
            $image('Image'),
            $status,
        ],
    ],

];