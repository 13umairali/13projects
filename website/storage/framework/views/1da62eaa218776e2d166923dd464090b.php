<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['title' => 'Dashboard', 'subtitle' => null]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['title' => 'Dashboard', 'subtitle' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<!DOCTYPE html>
<html
    lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>"
    dir="<?php echo e($textDirection ?? 'ltr'); ?>"
    x-data="adminLayout()"
    x-init="init()"
    :class="{ dark: theme === 'dark' }"
    :data-theme="theme"
    class="h-full overflow-hidden"
>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <meta name="robots" content="noindex, nofollow">
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    <?php $__siteName = \App\Models\SystemSetting::get('site_name', config('app.name', 'MailTrixy')); $__favicon = \App\Models\SystemSetting::get('favicon'); ?>
    <title><?php echo e($title); ?> — <?php echo e($__siteName); ?> Admin</title>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($__favicon): ?>
    <link rel="icon" href="<?php echo e(asset('storage/' . $__favicon)); ?>" type="image/png">
    <link rel="apple-touch-icon" href="<?php echo e(asset('storage/' . $__favicon)); ?>">
    <?php else: ?>
    <link rel="icon" href="<?php echo e(asset('favicon.ico')); ?>" sizes="any">
    <link rel="icon" href="<?php echo e(asset('images/dahimail-logo.png')); ?>" type="image/png">
    <link rel="apple-touch-icon" href="<?php echo e(asset('apple-touch-icon.png')); ?>">
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    


    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="<?php echo e(asset('vendor/chart.min.js')); ?>" defer></script>

    
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php if (isset($component)) { $__componentOriginal6328591391b02ca7d72027c0d6027f6b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6328591391b02ca7d72027c0d6027f6b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.brand-theme','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('brand-theme'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6328591391b02ca7d72027c0d6027f6b)): ?>
<?php $attributes = $__attributesOriginal6328591391b02ca7d72027c0d6027f6b; ?>
<?php unset($__attributesOriginal6328591391b02ca7d72027c0d6027f6b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6328591391b02ca7d72027c0d6027f6b)): ?>
<?php $component = $__componentOriginal6328591391b02ca7d72027c0d6027f6b; ?>
<?php unset($__componentOriginal6328591391b02ca7d72027c0d6027f6b); ?>
<?php endif; ?>
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>


    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.classList.toggle('dark', savedTheme === 'dark');
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
    
    <?php $__customCss = \App\Models\SystemSetting::get('custom_css', ''); ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(trim($__customCss) !== ''): ?>
        <style id="admin-custom-css"><?php echo $__customCss; ?></style>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php echo \App\Models\SystemSetting::get('head_code', ''); ?>

</head>
<body class="h-full overflow-hidden font-sans text-ink bg-surface antialiased">
<noscript>
    <div style="padding: 2rem; text-align: center; font-family: 'Inter', system-ui, sans-serif; background: #F8F9FC; min-height: 100vh; display: flex; align-items: center; justify-content: center;">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 700; color: #1E293B; margin-bottom: 0.5rem;"><?php echo e(__('JavaScript Required')); ?></h1>
            <p style="color: #9CA3AF; font-size: 0.875rem;"><?php echo e(__('This application requires JavaScript to function. Please enable JavaScript in your browser settings.')); ?></p>
        </div>
    </div>
</noscript>


<div id="mb-progress-bar"></div>

<div class="flex h-full min-h-0" x-data="{ sidebarOpen: false }">

    
    <aside
        class="admin-sidebar fixed inset-y-0 left-0 z-40 flex w-62 flex-col border-r border-border bg-surface-2/95 backdrop-blur-sm transition-transform duration-300 -translate-x-full lg:static lg:translate-x-0"
        :class="{ '!translate-x-0': sidebarOpen }"
        role="navigation"
        aria-label="Admin navigation"
    >
        
        <?php $__logoLight = \App\Models\SystemSetting::get('logo_light'); $__logoDark = \App\Models\SystemSetting::get('logo_dark'); ?>
        <div class="px-4 pt-5 pb-4 flex items-center justify-between gap-2">
            <a href="<?php echo e(url('/admin/dashboard')); ?>" class="flex items-center min-w-0" wire:navigate>
                <?php if (isset($component)) { $__componentOriginal8741a05e11b0c77d19ec61b6b35b26b3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8741a05e11b0c77d19ec61b6b35b26b3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.brand-logo','data' => ['name' => $__siteName]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('brand-logo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($__siteName)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8741a05e11b0c77d19ec61b6b35b26b3)): ?>
<?php $attributes = $__attributesOriginal8741a05e11b0c77d19ec61b6b35b26b3; ?>
<?php unset($__attributesOriginal8741a05e11b0c77d19ec61b6b35b26b3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8741a05e11b0c77d19ec61b6b35b26b3)): ?>
<?php $component = $__componentOriginal8741a05e11b0c77d19ec61b6b35b26b3; ?>
<?php unset($__componentOriginal8741a05e11b0c77d19ec61b6b35b26b3); ?>
<?php endif; ?>
            </a>
            
            <button type="button"
                    class="lg:hidden shrink-0 inline-flex items-center justify-center w-9 h-9 rounded-lg text-muted hover:text-ink hover:bg-surface-2 transition-colors"
                    @click="sidebarOpen = false"
                    aria-label="Close sidebar">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        
        <nav id="admin-sidebar-nav" class="flex-1 overflow-y-auto overflow-x-hidden px-3 pb-3 scrollbar-hide" aria-label="Admin sidebar">
            <div class="space-y-1">

                
                <a href="<?php echo e(url('/admin/dashboard')); ?>" wire:navigate class="nav-item <?php echo e(request()->is('admin/dashboard') ? 'nav-item-active' : ''); ?>" <?php if(request()->is('admin/dashboard')): ?> aria-current="page" <?php endif; ?>>
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="7" rx="1.5"></rect>
                        <rect x="14" y="3" width="7" height="7" rx="1.5"></rect>
                        <rect x="3" y="14" width="7" height="7" rx="1.5"></rect>
                        <rect x="14" y="14" width="7" height="7" rx="1.5"></rect>
                    </svg>
                    <span><?php echo e(__('Dashboard')); ?></span>
                </a>

                <a href="<?php echo e(url('/admin/users')); ?>" wire:navigate class="nav-item <?php echo e(request()->is('admin/users*') ? 'nav-item-active' : ''); ?>" <?php if(request()->is('admin/users*')): ?> aria-current="page" <?php endif; ?>>
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9" cy="8" r="3"></circle>
                        <path d="M3 19c1.8-3 4.8-4.5 6-4.5s4.2 1.5 6 4.5"></path>
                        <path d="M15.5 11a3 3 0 1 0 0-6"></path>
                        <path d="M18 19c.6-1.1 1.6-2.2 3-3"></path>
                    </svg>
                    <span><?php echo e(__('Users')); ?></span>
                </a>

                <a href="<?php echo e(url('/admin/tickets')); ?>" wire:navigate class="nav-item <?php echo e(request()->is('admin/tickets*') ? 'nav-item-active' : ''); ?>" <?php if(request()->is('admin/tickets*')): ?> aria-current="page" <?php endif; ?>>
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        <path d="M8 10h.01"></path>
                        <path d="M12 10h.01"></path>
                        <path d="M16 10h.01"></path>
                    </svg>
                    <span><?php echo e(__('Tickets')); ?></span>
                </a>

                
                <p class="mt-2 px-3 text-[11px] font-semibold uppercase tracking-widest text-muted/60"><?php echo e(__('Business')); ?></p>
                <div class="mt-2 space-y-1">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->is_admin && (auth()->user()->admin_role === 'super_admin' || auth()->user()->hasRole('Super Admin') || auth()->user()->can('plans.view'))): ?>
                    <a href="<?php echo e(url('/admin/plans')); ?>" wire:navigate class="nav-item <?php echo e(request()->is('admin/plans*') ? 'nav-item-active' : ''); ?>" <?php if(request()->is('admin/plans*')): ?> aria-current="page" <?php endif; ?>>
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                            <path d="M2 10h20"></path>
                        </svg>
                        <span><?php echo e(__('Plans')); ?></span>
                    </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('coupons.view')): ?>
                    <a href="<?php echo e(url('/admin/coupons')); ?>" wire:navigate class="nav-item <?php echo e(request()->is('admin/coupons*') ? 'nav-item-active' : ''); ?>" <?php if(request()->is('admin/coupons*')): ?> aria-current="page" <?php endif; ?>>
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2z"></path>
                            <path d="M13 5v2"></path>
                            <path d="M13 17v2"></path>
                            <path d="M13 11v2"></path>
                        </svg>
                        <span><?php echo e(__('Coupons')); ?></span>
                    </a>
                    <?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->is_admin && (auth()->user()->admin_role === 'super_admin' || auth()->user()->hasRole('Super Admin') || auth()->user()->can('payments.view'))): ?>
                    <a href="<?php echo e(url('/admin/payments')); ?>" wire:navigate class="nav-item <?php echo e(request()->is('admin/payments*') ? 'nav-item-active' : ''); ?>" <?php if(request()->is('admin/payments*')): ?> aria-current="page" <?php endif; ?>>
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                        </svg>
                        <span><?php echo e(__('Payments')); ?></span>
                    </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->is_admin && (auth()->user()->admin_role === 'super_admin' || auth()->user()->hasRole('Super Admin') || auth()->user()->can('payment-gateways.view'))): ?>
                    <a href="<?php echo e(url('/admin/payment-gateways')); ?>" wire:navigate class="nav-item <?php echo e(request()->is('admin/payment-gateways*') ? 'nav-item-active' : ''); ?>" <?php if(request()->is('admin/payment-gateways*')): ?> aria-current="page" <?php endif; ?>>
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                            <path d="M6 8h.01"></path>
                            <path d="M10 8h.01"></path>
                            <path d="M14 8h.01"></path>
                        </svg>
                        <span><?php echo e(__('Payment Gateways')); ?></span>
                    </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                
                <p class="mt-2 px-3 text-[11px] font-semibold uppercase tracking-widest text-muted/60"><?php echo e(__('Content')); ?></p>
                <div class="mt-2 space-y-1">
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('cms.view')): ?>
                    <a href="<?php echo e(url('/admin/cms')); ?>" wire:navigate class="nav-item <?php echo e(request()->is('admin/cms*') ? 'nav-item-active' : ''); ?>">
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                        <span><?php echo e(__('CMS')); ?></span>
                    </a>
                    <?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('frontend-settings.view')): ?>
                    <a href="<?php echo e(url('/admin/frontend-settings')); ?>" wire:navigate class="nav-item <?php echo e(request()->is('admin/frontend-settings*') ? 'nav-item-active' : ''); ?>">
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                        <span><?php echo e(__('Frontend')); ?></span>
                    </a>
                    <?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('notifications.view')): ?>
                    <a href="<?php echo e(url('/admin/notifications')); ?>" wire:navigate class="nav-item <?php echo e(request()->is('admin/notifications*') ? 'nav-item-active' : ''); ?>">
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                        <span><?php echo e(__('Notifications')); ?></span>
                    </a>
                    <?php endif; ?>
                </div>

                
                <p class="mt-2 px-3 text-[11px] font-semibold uppercase tracking-widest text-muted/60"><?php echo e(__('System')); ?></p>
                <div class="mt-2 space-y-1">
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('settings.view')): ?>
                    <a href="<?php echo e(url('/admin/settings')); ?>" wire:navigate class="nav-item <?php echo e(request()->is('admin/settings') ? 'nav-item-active' : ''); ?>">
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                        <span><?php echo e(__('Settings')); ?></span>
                    </a>
                    <?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->admin_role === 'super_admin' || auth()->user()->hasRole('Super Admin')): ?>
                    <a href="<?php echo e(url('/admin/phone-verification')); ?>" wire:navigate class="nav-item <?php echo e(request()->is('admin/phone-verification*') ? 'nav-item-active' : ''); ?>">
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                        <span><?php echo e(__('Phone Verification')); ?></span>
                    </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->admin_role === 'super_admin' || auth()->user()->hasRole('Super Admin')): ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->admin_role === 'super_admin'): ?>
                    <a href="<?php echo e(route('admin.broadcasts.index')); ?>" class="nav-item"><?php echo e(__('Broadcasts · push & email')); ?></a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <a href="<?php echo e(url('/admin/friends-settings')); ?>" wire:navigate class="nav-item <?php echo e(request()->is('admin/friends-settings*') ? 'nav-item-active' : ''); ?>">
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <span><?php echo e(__('Friends Settings')); ?></span>
                    </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ai-providers.view')): ?>
                    <a href="<?php echo e(url('/admin/ai-providers')); ?>" wire:navigate class="nav-item <?php echo e(request()->is('admin/ai-providers*') ? 'nav-item-active' : ''); ?>">
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a7 7 0 0 1 7 7c0 2.4-1.2 4.5-3 5.7V17a2 2 0 0 1-2 2h-4a2 2 0 0 1-2-2v-2.3C6.2 13.5 5 11.4 5 9a7 7 0 0 1 7-7z"/><path d="M9 22h6"/></svg>
                        <span><?php echo e(__('AI Providers')); ?></span>
                    </a>
                    <?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ai-usage.view')): ?>
                    <a href="<?php echo e(url('/admin/ai-usage')); ?>" wire:navigate class="nav-item <?php echo e(request()->is('admin/ai-usage*') ? 'nav-item-active' : ''); ?>">
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 14l4-4 3 3 5-6"/></svg>
                        <span><?php echo e(__('AI Usage')); ?></span>
                    </a>
                    <?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('temp-mail.view')): ?>
                    <a href="<?php echo e(url('/admin/temp-mail')); ?>" wire:navigate class="nav-item <?php echo e(request()->is('admin/temp-mail*') ? 'nav-item-active' : ''); ?>">
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/><line x1="2" y1="20" x2="8" y2="14"/><line x1="22" y1="20" x2="16" y2="14"/></svg>
                        <span><?php echo e(__('Temp Mail')); ?></span>
                    </a>
                    <?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('roles.view')): ?>
                    <a href="<?php echo e(url('/admin/roles')); ?>" wire:navigate class="nav-item <?php echo e(request()->is('admin/roles*') ? 'nav-item-active' : ''); ?>">
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        <span><?php echo e(__('Roles')); ?></span>
                    </a>
                    <?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('permissions.view')): ?>
                    <a href="<?php echo e(url('/admin/permissions')); ?>" wire:navigate class="nav-item <?php echo e(request()->is('admin/permissions*') ? 'nav-item-active' : ''); ?>">
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                        <span><?php echo e(__('Permissions')); ?></span>
                    </a>
                    <?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('audit-logs.view')): ?>
                    <a href="<?php echo e(url('/admin/audit-log')); ?>" wire:navigate class="nav-item <?php echo e(request()->is('admin/audit-log*') ? 'nav-item-active' : ''); ?>">
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/></svg>
                        <span><?php echo e(__('Audit Log')); ?></span>
                    </a>
                    <?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('security-audit-logs.view')): ?>
                    <a href="<?php echo e(url('/admin/security-audit-logs')); ?>" wire:navigate class="nav-item <?php echo e(request()->is('admin/security-audit-logs*') ? 'nav-item-active' : ''); ?>">
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg>
                        <span><?php echo e(__('Security Logs')); ?></span>
                    </a>
                    <?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('security-settings.view')): ?>
                    <a href="<?php echo e(url('/admin/security-settings')); ?>" wire:navigate class="nav-item <?php echo e(request()->is('admin/security-settings*') ? 'nav-item-active' : ''); ?>">
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <span><?php echo e(__('Security Settings')); ?></span>
                    </a>
                    <?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('blocked-ips.view')): ?>
                    <a href="<?php echo e(url('/admin/blocked-ips')); ?>" wire:navigate class="nav-item <?php echo e(request()->is('admin/blocked-ips*') ? 'nav-item-active' : ''); ?>">
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M5.7 5.7l12.6 12.6"/></svg>
                        <span><?php echo e(__('Blocked IPs')); ?></span>
                    </a>
                    <?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->admin_role === 'super_admin' || auth()->user()->hasRole('Super Admin')): ?>
                    <a href="<?php echo e(url('/admin/sending-limits')); ?>" wire:navigate class="nav-item <?php echo e(request()->is('admin/sending-limits*') ? 'nav-item-active' : ''); ?>">
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/></svg>
                        <span><?php echo e(__('Sending limits')); ?></span>
                    </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('blocked-locations.view')): ?>
                    <a href="<?php echo e(url('/admin/blocked-locations')); ?>" wire:navigate class="nav-item <?php echo e(request()->is('admin/blocked-locations*') ? 'nav-item-active' : ''); ?>">
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        <span><?php echo e(__('Blocked Locations')); ?></span>
                    </a>
                    <?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('system-settings.view')): ?>
                    <a href="<?php echo e(url('/admin/languages')); ?>" wire:navigate class="nav-item <?php echo e(request()->is('admin/languages*') ? 'nav-item-active' : ''); ?>">
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                        <span><?php echo e(__('Languages')); ?></span>
                    </a>
                    <a href="<?php echo e(url('/admin/currencies')); ?>" wire:navigate class="nav-item <?php echo e(request()->is('admin/currencies*') ? 'nav-item-active' : ''); ?>">
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                        <span><?php echo e(__('Currencies')); ?></span>
                    </a>
                    <?php endif; ?>
                    <a href="<?php echo e(url('/admin/email-deliverability')); ?>" wire:navigate class="nav-item <?php echo e(request()->is('admin/email-deliverability*') ? 'nav-item-active' : ''); ?>">
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        <span><?php echo e(__('Email Health')); ?></span>
                    </a>
                    <a href="<?php echo e(url('/admin/system')); ?>" wire:navigate class="nav-item <?php echo e(request()->is('admin/system') ? 'nav-item-active' : ''); ?>">
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                        <span><?php echo e(__('System Info')); ?></span>
                    </a>
                    
                    <?php
                        $u = auth()->user();
                        $isSuperAdmin = $u && (
                            (method_exists($u, 'hasRole') && $u->hasRole('Super Admin'))
                            || ($u->is_admin && ($u->admin_role ?? '') === 'super_admin')
                        );
                    ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isSuperAdmin): ?>
                    <a href="<?php echo e(url('/admin/update')); ?>" wire:navigate class="nav-item <?php echo e(request()->is('admin/update*') ? 'nav-item-active' : ''); ?>">
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-3-6.7L21 8"/><path d="M21 3v5h-5"/></svg>
                        <span class="flex-1"><?php echo e(__('System Update')); ?></span>
                        <span class="text-[9px] font-semibold uppercase tracking-wider bg-brand/15 text-brand px-1.5 py-0.5 rounded">v<?php echo e(config('version.version', '1.0.0')); ?></span>
                    </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </nav>

        
        <div class="border-t border-border/40 px-4 py-3 shrink-0">
            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand/15 text-brand text-xs font-bold">
                    <?php echo e(strtoupper(substr(auth()->user()->name ?? 'A', 0, 1))); ?>

                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold truncate leading-tight text-ink"><?php echo e(auth()->user()->name ?? 'Admin'); ?></p>
                    <p class="text-[10px] font-semibold uppercase tracking-[0.15em] text-muted truncate leading-tight"><?php echo e(auth()->user()->admin_role ?? 'Super Admin'); ?></p>
                </div>
                <form method="POST" action="<?php echo e(route('logout')); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-muted hover:text-danger hover:bg-danger/10 transition" aria-label="Logout">
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    
    <div
        x-show="sidebarOpen"
        @click="sidebarOpen = false"
        class="fixed inset-0 z-30 bg-black/60 backdrop-blur-sm lg:hidden"
        x-transition.opacity
        style="display: none;"
    ></div>

    
    <div class="flex flex-1 flex-col overflow-y-auto">

        
        <header class="sticky top-0 z-30 border-b border-border/60 bg-surface-2/90 backdrop-blur">
            <div class="flex items-center justify-between px-4 py-4 lg:px-6">

                
                <div class="flex min-w-0 flex-1 items-center gap-3">
                    
                    <button type="button" class="icon-button shrink-0 lg:hidden" @click="sidebarOpen = !sidebarOpen" aria-label="Toggle sidebar">
                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>

                    <div class="min-w-0 flex-1">
                        <p class="panel-heading"><?php echo e(__('Admin Console')); ?></p>
                        <h1 class="truncate text-lg font-semibold tracking-tight sm:text-2xl text-ink"><?php echo e($title); ?></h1>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($subtitle): ?>
                            <p class="text-sm text-muted mt-0.5 truncate"><?php echo e($subtitle); ?></p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                
                <div class="flex shrink-0 items-center gap-3">
                    
                    <div class="hidden md:block relative" x-data="{
                        query: '',
                        results: { users: [], plans: [], tickets: [] },
                        loading: false,
                        open: false,
                        debounceTimer: null,
                        async search() {
                            if (this.query.trim().length < 2) { this.results = { users: [], plans: [], tickets: [] }; this.open = false; return; }
                            this.loading = true;
                            this.open = true;
                            clearTimeout(this.debounceTimer);
                            this.debounceTimer = setTimeout(async () => {
                                try {
                                    const q = encodeURIComponent(this.query.trim());
                                    const [usersRes, plansRes, ticketsRes] = await Promise.all([
                                        fetch('/admin/users?search=' + q, { headers: { 'X-Requested-With': 'XMLHttpRequest' } }).then(r => r.text()),
                                        Promise.resolve(''),
                                        Promise.resolve('')
                                    ]);
                                    // Parse user names from HTML response
                                    const parser = new DOMParser();
                                    const doc = parser.parseFromString(usersRes, 'text/html');
                                    const rows = doc.querySelectorAll('tbody tr td .font-semibold');
                                    let users = [];
                                    rows.forEach((el, i) => {
                                        if (i < 5) {
                                            const link = el.closest('a');
                                            const emailEl = el.parentElement?.querySelector('.text-muted');
                                            users.push({
                                                name: el.textContent.trim(),
                                                email: emailEl ? emailEl.textContent.trim() : '',
                                                url: link ? link.getAttribute('href') : '#'
                                            });
                                        }
                                    });
                                    this.results = { users, plans: [], tickets: [] };
                                } catch(e) { console.error(e); }
                                this.loading = false;
                            }, 300);
                        },
                        clear() { this.query = ''; this.open = false; this.results = { users: [], plans: [], tickets: [] }; }
                    }" @click.outside="open = false">
                        <div class="relative">
                            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-muted pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input type="text" x-model="query" @input="search()" @focus="if(query.length >= 2) open = true"
                                   @keydown.enter.prevent="if(query.trim()) window.location.href='<?php echo e(url('/admin/users')); ?>?search=' + encodeURIComponent(query.trim())"
                                   @keydown.escape="clear()"
                                   placeholder="<?php echo e(__('Search people, events, uploads...')); ?>"
                                   class="h-10 w-72 rounded-xl border border-border bg-surface pl-10 pr-9 text-sm text-ink placeholder:text-muted/60 focus:border-brand focus:ring-2 focus:ring-brand/30 focus:outline-none transition-all">
                            <button x-show="query.length > 0" x-cloak @click="clear()" class="absolute right-3 top-1/2 -translate-y-1/2 text-muted hover:text-ink">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        
                        <div x-show="open" x-transition class="absolute left-0 right-0 mt-2 rounded-2xl border border-border bg-surface-2 shadow-xl z-50 overflow-hidden" style="display:none">
                            
                            <div x-show="loading" class="px-4 py-3 text-center">
                                <svg class="animate-spin h-4 w-4 text-brand mx-auto" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            </div>

                            
                            <template x-if="results.users.length > 0">
                                <div>
                                    <p class="px-4 pt-3 pb-1 text-[10px] font-semibold uppercase tracking-widest text-muted"><?php echo e(__('Users')); ?></p>
                                    <template x-for="user in results.users" :key="user.url">
                                        <a :href="user.url" class="flex items-center gap-3 px-4 py-2.5 hover:bg-surface transition-colors">
                                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand/10 text-brand text-xs font-bold" x-text="user.name.charAt(0).toUpperCase()"></span>
                                            <div class="min-w-0">
                                                <p class="text-sm font-semibold text-ink truncate" x-text="user.name"></p>
                                                <p class="text-xs text-muted truncate" x-text="user.email"></p>
                                            </div>
                                        </a>
                                    </template>
                                </div>
                            </template>

                            
                            <div x-show="!loading && results.users.length === 0 && query.length >= 2" class="px-4 py-4 text-center">
                                <p class="text-sm text-muted"><?php echo e(__('No results for')); ?> "<span class="font-medium text-ink" x-text="query"></span>"</p>
                            </div>

                            
                            <a x-show="query.length >= 2" :href="'/admin/users?search=' + encodeURIComponent(query)" class="flex items-center justify-center gap-2 px-4 py-2.5 border-t border-border/60 text-xs font-semibold text-brand hover:bg-brand/5 transition-colors">
                                <?php echo e(__('View all results')); ?>

                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>

                    
                    <button class="icon-button" title="Toggle fullscreen" @click="
                        if (!document.fullscreenElement) {
                            document.documentElement.requestFullscreen();
                        } else {
                            document.exitFullscreen();
                        }
                    ">
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path>
                        </svg>
                    </button>

                    
                    <?php if (isset($component)) { $__componentOriginal8d3bff7d7383a45350f7495fc470d934 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8d3bff7d7383a45350f7495fc470d934 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.language-switcher','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('language-switcher'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8d3bff7d7383a45350f7495fc470d934)): ?>
<?php $attributes = $__attributesOriginal8d3bff7d7383a45350f7495fc470d934; ?>
<?php unset($__attributesOriginal8d3bff7d7383a45350f7495fc470d934); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8d3bff7d7383a45350f7495fc470d934)): ?>
<?php $component = $__componentOriginal8d3bff7d7383a45350f7495fc470d934; ?>
<?php unset($__componentOriginal8d3bff7d7383a45350f7495fc470d934); ?>
<?php endif; ?>

                    <?php if (isset($component)) { $__componentOriginal2090438866f3dcdb76cd8b070bcc302d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2090438866f3dcdb76cd8b070bcc302d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.theme-toggle','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('theme-toggle'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2090438866f3dcdb76cd8b070bcc302d)): ?>
<?php $attributes = $__attributesOriginal2090438866f3dcdb76cd8b070bcc302d; ?>
<?php unset($__attributesOriginal2090438866f3dcdb76cd8b070bcc302d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2090438866f3dcdb76cd8b070bcc302d)): ?>
<?php $component = $__componentOriginal2090438866f3dcdb76cd8b070bcc302d; ?>
<?php unset($__componentOriginal2090438866f3dcdb76cd8b070bcc302d); ?>
<?php endif; ?>

                    
                    <?php
                        $openTickets = \Illuminate\Support\Facades\DB::table('tickets')->where('status', 'open')->count();
                        $recentUsers = \App\Models\User::where('created_at', '>=', now()->subDay())->count();
                        $failedPayments = \Illuminate\Support\Facades\DB::table('payments')->where('status', 'failed')->where('created_at', '>=', now()->subDays(7))->count();
                        $pendingJobs = \Illuminate\Support\Facades\DB::table('failed_jobs')->count();
                        $newWorkspaces = \Illuminate\Support\Facades\DB::table('workspaces')->where('created_at', '>=', now()->subDay())->count();
                        $activeNow = \Illuminate\Support\Facades\DB::table('sessions')->where('last_activity', '>=', now()->subMinutes(5)->timestamp)->count();
                        $notifCount = $openTickets + $recentUsers + $failedPayments + $pendingJobs;
                    ?>
                    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                        <button @click="open = !open" class="icon-button relative" aria-label="Notifications">
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"></path><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"></path></svg>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($notifCount > 0): ?>
                            <span class="absolute -top-0.5 -right-0.5 flex h-5 w-5 items-center justify-center rounded-full bg-danger text-[10px] font-bold text-white ring-2 ring-surface-2"><?php echo e($notifCount > 9 ? '9+' : $notifCount); ?></span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </button>
                        <div
                            x-show="open"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-80 rounded-2xl border border-border bg-surface-2 shadow-xl z-50"
                            style="display: none;"
                        >
                            <div class="flex items-center justify-between px-4 py-3 border-b border-border/60">
                                <p class="text-sm font-semibold text-ink"><?php echo e(__('Notifications')); ?></p>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($notifCount > 0): ?>
                                <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-danger/10 text-danger"><?php echo e($notifCount); ?> <?php echo e(__('new')); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="max-h-72 overflow-y-auto scrollbar-hide">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($openTickets > 0): ?>
                                <a href="<?php echo e(url('/admin/tickets')); ?>" class="flex items-start gap-3 px-4 py-3 hover:bg-surface transition-colors border-b border-border/40">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-warning/10 text-warning mt-0.5">
                                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-ink"><?php echo e($openTickets); ?> <?php echo e(__('open ticket')); ?><?php echo e($openTickets > 1 ? 's' : ''); ?></p>
                                        <p class="text-xs text-muted"><?php echo e(__('Awaiting response')); ?></p>
                                    </div>
                                </a>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($recentUsers > 0): ?>
                                <a href="<?php echo e(url('/admin/users')); ?>" class="flex items-start gap-3 px-4 py-3 hover:bg-surface transition-colors border-b border-border/40">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-success/10 text-success mt-0.5">
                                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-ink"><?php echo e($recentUsers); ?> <?php echo e(__('new user')); ?><?php echo e($recentUsers > 1 ? 's' : ''); ?> <?php echo e(__('today')); ?></p>
                                        <p class="text-xs text-muted"><?php echo e(__('Registered in the last 24h')); ?></p>
                                    </div>
                                </a>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($failedPayments > 0): ?>
                                <a href="<?php echo e(url('/admin/payments?status=failed')); ?>" class="flex items-start gap-3 px-4 py-3 hover:bg-surface transition-colors border-b border-border/40">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-danger/10 text-danger mt-0.5">
                                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-ink"><?php echo e($failedPayments); ?> <?php echo e(__('failed payment')); ?><?php echo e($failedPayments > 1 ? 's' : ''); ?></p>
                                        <p class="text-xs text-muted"><?php echo e(__('In the last 7 days')); ?></p>
                                    </div>
                                </a>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pendingJobs > 0): ?>
                                <a href="<?php echo e(url('/admin/system')); ?>" class="flex items-start gap-3 px-4 py-3 hover:bg-surface transition-colors border-b border-border/40">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-danger/10 text-danger mt-0.5">
                                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-ink"><?php echo e($pendingJobs); ?> <?php echo e(__('failed job')); ?><?php echo e($pendingJobs > 1 ? 's' : ''); ?></p>
                                        <p class="text-xs text-muted"><?php echo e(__('Requires attention')); ?></p>
                                    </div>
                                </a>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($newWorkspaces > 0): ?>
                                <a href="<?php echo e(url('/admin/users')); ?>" class="flex items-start gap-3 px-4 py-3 hover:bg-surface transition-colors border-b border-border/40">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand/10 text-brand mt-0.5">
                                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-ink"><?php echo e($newWorkspaces); ?> <?php echo e(__('new workspace')); ?><?php echo e($newWorkspaces > 1 ? 's' : ''); ?></p>
                                        <p class="text-xs text-muted"><?php echo e(__('Created in the last 24h')); ?></p>
                                    </div>
                                </a>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                
                                <div class="flex items-start gap-3 px-4 py-3 border-b border-border/40">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-success/10 text-success mt-0.5">
                                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3" fill="currentColor"/></svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-ink"><?php echo e($activeNow); ?> <?php echo e(__('user')); ?><?php echo e($activeNow !== 1 ? 's' : ''); ?> <?php echo e(__('online now')); ?></p>
                                        <p class="text-xs text-muted"><?php echo e(__('Active in the last 5 minutes')); ?></p>
                                    </div>
                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($notifCount === 0): ?>
                                <div class="px-4 py-4 text-center">
                                    <p class="text-xs text-muted"><?php echo e(__('No urgent notifications')); ?></p>
                                </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <a href="<?php echo e(url('/admin/notifications')); ?>" class="flex items-center justify-center gap-2 px-4 py-2.5 border-t border-border/60 text-xs font-semibold uppercase tracking-wider text-brand hover:bg-brand/5 transition-colors rounded-b-2xl">
                                <?php echo e(__('View all notifications')); ?>

                            </a>
                        </div>
                    </div>

                    
                    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                        <button @click="open = !open" class="flex items-center gap-2.5 transition" aria-label="User menu" aria-haspopup="true" :aria-expanded="open">
                            <div class="text-right hidden sm:block">
                                <p class="text-sm font-semibold text-ink"><?php echo e(auth()->user()->name ?? 'Admin'); ?></p>
                                <p class="text-[10px] text-muted"><?php echo e(__('Super Admin')); ?></p>
                            </div>
                            <div class="h-9 w-9 rounded-full bg-brand/15 flex items-center justify-center text-sm font-bold text-brand">
                                <?php echo e(strtoupper(substr(auth()->user()->name ?? 'A', 0, 1))); ?>

                            </div>
                        </button>
                        <div
                            x-show="open"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-56 rounded-2xl border border-border/70 bg-surface-2 p-2 shadow-soft z-50"
                            style="display: none;"
                        >
                            <div class="px-3 py-2 border-b border-border/60 mb-1">
                                <p class="text-sm font-semibold text-ink truncate"><?php echo e(auth()->user()->name ?? 'Admin'); ?></p>
                                <p class="text-[11px] text-muted truncate"><?php echo e(auth()->user()->email ?? ''); ?></p>
                            </div>
                            <a href="<?php echo e(url('/dashboard')); ?>" wire:navigate class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm text-ink transition hover:bg-muted/10">
                                <svg viewBox="0 0 24 24" class="h-4 w-4 text-ink/60" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="3" width="7" height="7" rx="1.5"></rect><rect x="14" y="3" width="7" height="7" rx="1.5"></rect><rect x="3" y="14" width="7" height="7" rx="1.5"></rect><rect x="14" y="14" width="7" height="7" rx="1.5"></rect></svg>
                                <?php echo e(__('User Dashboard')); ?>

                            </a>
                            <a href="<?php echo e(url('/admin/settings')); ?>" wire:navigate class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm text-ink transition hover:bg-muted/10">
                                <svg viewBox="0 0 24 24" class="h-4 w-4 text-ink/60" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09"></path></svg>
                                <?php echo e(__('Settings')); ?>

                            </a>
                            <div class="my-1 border-t border-border/60"></div>
                            <form method="POST" action="<?php echo e(route('logout')); ?>">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2 text-sm text-danger transition hover:bg-danger/10">
                                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                                    <?php echo e(__('Sign Out')); ?>

                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        
        <main class="flex-1 px-4 py-6 lg:px-8 overflow-y-auto">
            <div class="max-w-7xl mx-auto">
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                 role="alert" aria-live="polite"
                 class="mb-4 alert alert-success">
                <svg viewBox="0 0 24 24" class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                <span class="flex-1 text-sm font-medium"><?php echo e(session('success')); ?></span>
                <button @click="show = false" class="opacity-60 hover:opacity-100" aria-label="Dismiss">&times;</button>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
            <div x-data="{ show: true }" x-show="show"
                 role="alert" aria-live="assertive"
                 class="mb-4 alert alert-error">
                <svg viewBox="0 0 24 24" class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                <span class="flex-1 text-sm font-medium"><?php echo e(session('error')); ?></span>
                <button @click="show = false" class="opacity-60 hover:opacity-100" aria-label="Dismiss">&times;</button>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php echo e($slot); ?>


            </div>
        </main>
    </div>
</div>

<?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>


<script>
function adminLayout() {
    return {
        theme: localStorage.getItem('theme') || 'light',

        init() {
            document.documentElement.setAttribute('data-theme', this.theme);
            document.documentElement.classList.toggle('dark', this.theme === 'dark');
        },

        toggleTheme() {
            this.theme = this.theme === 'light' ? 'dark' : 'light';
            localStorage.setItem('theme', this.theme);
            document.documentElement.setAttribute('data-theme', this.theme);
            document.documentElement.classList.toggle('dark', this.theme === 'dark');
        },
    };
}

// wire:navigate progress bar
(function () {
    const bar = document.getElementById('mb-progress-bar');
    let timer = null;

    function start() {
        if (!bar) return;
        clearTimeout(timer);
        bar.style.transition = 'none';
        bar.style.width = '0%';
        bar.classList.add('active');
        bar.offsetHeight;
        bar.style.transition = 'width 300ms ease';
        bar.style.width = '60%';
        timer = setTimeout(() => { bar.style.width = '80%'; }, 400);
    }

    function finish() {
        if (!bar) return;
        clearTimeout(timer);
        bar.style.transition = 'width 200ms ease';
        bar.style.width = '100%';
        setTimeout(() => {
            bar.style.transition = 'opacity 300ms ease';
            bar.classList.remove('active');
            setTimeout(() => { bar.style.width = '0%'; }, 350);
        }, 200);
    }

    document.addEventListener('livewire:navigating', start);
    document.addEventListener('livewire:navigated', finish);

    // Page enter animation
    document.addEventListener('livewire:navigated', () => {
        const main = document.querySelector('main');
        if (!main || !main.firstElementChild) return;
        const el = main.firstElementChild;
        el.style.animation = 'none';
        el.offsetHeight;
        el.style.animation = '';
    });

    // Re-initialize Lucide icons on SPA navigation
    document.addEventListener('livewire:navigated', function() {
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });

    // Fix dark mode persistence on wire:navigate SPA transitions
    document.addEventListener('livewire:navigated', function() {
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.classList.toggle('dark', savedTheme === 'dark');
        document.documentElement.setAttribute('data-theme', savedTheme);
    });
})();
</script>

<script>if(typeof lucide !== 'undefined') lucide.createIcons();</script>
<script>
(function() {
    var nav = document.getElementById('admin-sidebar-nav');
    if (!nav) return;
    var saved = sessionStorage.getItem('admin-sidebar-scroll');
    if (saved) nav.scrollTop = parseInt(saved, 10);
    nav.addEventListener('scroll', function() { sessionStorage.setItem('admin-sidebar-scroll', nav.scrollTop); });
    document.addEventListener('livewire:navigating', function() { sessionStorage.setItem('admin-sidebar-scroll', nav.scrollTop); });
})();
</script>


<?php echo \App\Models\SystemSetting::get('footer_code', ''); ?>

    <?php echo $__env->make('partials.timezone-sync', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>
</html><?php /**PATH /home/dahimail.com/public_html/resources/views/components/layouts/admin.blade.php ENDPATH**/ ?>