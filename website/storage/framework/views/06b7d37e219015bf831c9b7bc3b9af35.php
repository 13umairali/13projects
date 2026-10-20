<?php if (isset($component)) { $__componentOriginalc8c9fd5d7827a77a31381de67195f0c3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc8c9fd5d7827a77a31381de67195f0c3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.admin','data' => ['title' => __('System Settings'),'subtitle' => __('Configure branding, authentication, integrations, and more.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.admin'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('System Settings')),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Configure branding, authentication, integrations, and more.'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <div class="space-y-6" x-data="settingsPage()">

        
        <div class="flex items-center justify-end">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(app()->isDownForMaintenance()): ?>
            <span class="inline-flex items-center gap-2 px-4 py-2 text-xs font-bold text-danger bg-danger/10 border border-danger/20 rounded-xl">
                <span class="w-2 h-2 bg-danger rounded-full animate-pulse"></span>
                <?php echo e(__('Maintenance Mode')); ?>

            </span>
            <?php else: ?>
            <span class="inline-flex items-center gap-2 px-4 py-2 text-xs font-bold text-success bg-success/10 border border-success/20 rounded-xl">
                <span class="w-2 h-2 bg-success rounded-full animate-pulse"></span>
                <?php echo e(__('System Online')); ?>

            </span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
             class="p-4 bg-success/10 border border-success/20 rounded-xl text-sm font-medium text-success flex items-center gap-2">
            <svg class="w-5 h-5 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <?php echo e(session('success')); ?>

            <button @click="show = false" class="ml-auto opacity-60 hover:opacity-100">&times;</button>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
        <div x-data="{ show: true }" x-show="show"
             class="p-4 bg-danger/10 border border-danger/20 rounded-xl text-sm font-medium text-danger flex items-center gap-2">
            <svg class="w-5 h-5 text-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <?php echo e(session('error')); ?>

            <button @click="show = false" class="ml-auto opacity-60 hover:opacity-100">&times;</button>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <div class="panel p-1.5">
            <div class="flex flex-wrap gap-1">
                <?php
                $tabs = [
                    ['id' => 'general',        'label' => __('General'),        'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
                    ['id' => 'branding',       'label' => __('Branding'),       'icon' => 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01'],
                    ['id' => 'email',          'label' => __('SMTP'),   'icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                    ['id' => 'auth',           'label' => __('Authentication'), 'icon' => 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z'],
                    ['id' => 'security',       'label' => __('Security'),       'icon' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'],
                    // AI Config tab removed — single source of truth lives at /admin/ai-providers
                    ['id' => 'integrations',   'label' => __('Integrations'),   'icon' => 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z'],
                    ['id' => 'legal',          'label' => __('GDPR'),   'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                    ['id' => 'pwa',            'label' => __('PWA'),            'icon' => 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z'],
                    ['id' => 'custom_code',    'label' => __('Custom Code'),    'icon' => 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4'],
                    ['id' => 'maintenance',    'label' => __('Maintenance'),    'icon' => 'M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4'],
                ];
                ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                <button @click="activeTab = '<?php echo e($tab['id']); ?>'"
                        :class="activeTab === '<?php echo e($tab['id']); ?>' ? 'bg-brand text-white shadow-soft' : 'text-muted hover:bg-surface hover:text-ink'"
                        class="flex items-center gap-2 px-4 py-2.5 text-sm font-semibold rounded-xl transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo e($tab['icon']); ?>"/></svg>
                    <span class="hidden sm:inline"><?php echo e($tab['label']); ?></span>
                </button>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        </div>

        <?php
            $s = fn(string $key, string $default = '') => \App\Models\SystemSetting::get($key, $default);
        ?>

        
        <div x-show="activeTab === 'general'" x-transition>
            <form method="POST" action="<?php echo e(route('admin.settings.update')); ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="_settings_group" value="general">
                <div class="panel overflow-hidden">
                    <div class="px-6 py-4 border-b border-border/50 bg-surface/50">
                        <h2 class="text-lg font-bold text-ink"><?php echo e(__('General Settings')); ?></h2>
                        <p class="text-sm text-muted mt-0.5"><?php echo e(__('Core platform identity and localization configuration.')); ?></p>
                    </div>
                    <div class="p-6 space-y-5">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                            <div>
                                <label class="settings-label"><?php echo e(__('Site Name')); ?></label>
                                <input type="text" name="site_name" value="<?php echo e($s('site_name', 'MailTrixy')); ?>" class="settings-input">
                            </div>
                            <div>
                                <label class="settings-label"><?php echo e(__('Tagline')); ?></label>
                                <input type="text" name="site_tagline" value="<?php echo e($s('site_tagline', 'AI-Powered Communication Automation')); ?>" class="settings-input">
                            </div>
                        </div>
                        <div>
                            <label class="settings-label"><?php echo e(__('Site Description')); ?></label>
                            <textarea rows="3" name="site_description" class="settings-input resize-none"><?php echo e($s('site_description', 'MailTrixy is a next-generation AI-powered communication automation platform.')); ?></textarea>
                        </div>
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                            <div>
                                <label class="settings-label"><?php echo e(__('Default Language')); ?></label>
                                <select name="default_language" class="settings-select">
                                    <?php $lang = $s('default_language', 'en'); ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['en' => 'English', 'es' => 'Spanish', 'fr' => 'French', 'de' => 'German', 'pt' => 'Portuguese', 'ja' => 'Japanese', 'zh' => 'Chinese', 'ar' => 'Arabic', 'hi' => 'Hindi', 'ko' => 'Korean']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                    <option value="<?php echo e($code); ?>" <?php echo e($lang === $code ? 'selected' : ''); ?>><?php echo e($name); ?></option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </select>
                            </div>
                            <div>
                                <label class="settings-label"><?php echo e(__('Default Timezone')); ?></label>
                                <select name="timezone" class="settings-select">
                                    <?php $tz = $s('timezone', 'UTC'); ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['UTC','America/New_York','America/Chicago','America/Denver','America/Los_Angeles','America/Sao_Paulo','Europe/London','Europe/Paris','Europe/Berlin','Europe/Moscow','Asia/Dubai','Asia/Kolkata','Asia/Shanghai','Asia/Tokyo','Asia/Seoul','Australia/Sydney','Pacific/Auckland']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $zone): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                    <option value="<?php echo e($zone); ?>" <?php echo e($tz === $zone ? 'selected' : ''); ?>><?php echo e($zone); ?></option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </select>
                            </div>
                            <div>
                                <label class="settings-label"><?php echo e(__('Default Currency')); ?></label>
                                <select name="default_currency" class="settings-select">
                                    <?php $cur = $s('default_currency', 'USD'); ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['USD' => 'USD ($)', 'EUR' => 'EUR (€)', 'GBP' => 'GBP (£)', 'INR' => 'INR (₹)', 'JPY' => 'JPY (¥)', 'CAD' => 'CAD (C$)', 'AUD' => 'AUD (A$)', 'BRL' => 'BRL (R$)']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                    <option value="<?php echo e($code); ?>" <?php echo e($cur === $code ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                            <div>
                                <label class="settings-label"><?php echo e(__('Support Email')); ?></label>
                                <input type="email" name="support_email" value="<?php echo e($s('support_email', 'support@mailtrixy.com')); ?>" class="settings-input" placeholder="support@yourdomain.com">
                                <p class="settings-hint"><?php echo e(__('Displayed in footer, error pages, and transactional emails.')); ?></p>
                            </div>
                            <div>
                                <label class="settings-label"><?php echo e(__('Support Phone')); ?></label>
                                <input type="text" name="support_phone" value="<?php echo e($s('support_phone')); ?>" class="settings-input" placeholder="+1 (555) 123-4567">
                                <p class="settings-hint"><?php echo e(__('Optional. Shown on contact and support pages.')); ?></p>
                            </div>
                        </div>
                        <div class="flex justify-end pt-2">
                            <button type="submit" class="settings-save-btn">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <?php echo e(__('Save General Settings')); ?>

                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        
        <div x-show="activeTab === 'branding'" x-transition style="display: none;">
            <form method="POST" action="<?php echo e(route('admin.settings.update')); ?>" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="_settings_group" value="branding">
                <div class="panel overflow-hidden">
                    <div class="px-6 py-4 border-b border-border/50 bg-surface/50">
                        <h2 class="text-lg font-bold text-ink"><?php echo e(__('Branding')); ?></h2>
                        <p class="text-sm text-muted mt-0.5"><?php echo e(__('Customize colors, logos, and favicon for your platform.')); ?></p>
                    </div>
                    <div class="p-6 space-y-6">
                        
                        <div>
                            <h3 class="text-sm font-bold text-ink uppercase tracking-wider mb-4"><?php echo e(__('Brand Colors')); ?></h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = [
                                    ['name' => 'primary_color', 'label' => __('Primary Color'), 'default' => '#4F46E5'],
                                    ['name' => 'secondary_color', 'label' => __('Secondary Color'), 'default' => '#7C3AED'],
                                    ['name' => 'accent_color', 'label' => __('Accent Color'), 'default' => '#06B6D4'],
                                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $color): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                <div x-data="{ hex: '<?php echo e($s($color['name'], $color['default'])); ?>' }">
                                    <label class="settings-label"><?php echo e($color['label']); ?></label>
                                    <div class="flex items-center gap-3">
                                        <input type="color" name="<?php echo e($color['name']); ?>" x-model="hex" class="w-12 h-12 rounded-xl border border-border cursor-pointer p-1">
                                        <input type="text" x-model="hex" class="flex-1 settings-input font-mono text-xs" maxlength="7">
                                    </div>
                                    <div class="mt-2 h-2 rounded-full" :style="'background-color:' + hex"></div>
                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </div>
                        </div>

                        <div class="border-t border-border/50"></div>

                        
                        <div>
                            <h3 class="text-sm font-bold text-ink uppercase tracking-wider mb-4"><?php echo e(__('Logos & Favicon')); ?></h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                                <div>
                                    <label class="settings-label"><?php echo e(__('Logo (Light Mode)')); ?></label>
                                    <div class="flex items-center gap-4">
                                        <div class="w-16 h-16 bg-gray-800 rounded-2xl flex items-center justify-center shadow-lg border border-border/30 overflow-hidden">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($s('logo_light')): ?>
                                                <img src="<?php echo e(asset('storage/' . $s('logo_light'))); ?>" alt="Logo" class="h-12 w-12 object-contain">
                                            <?php else: ?>
                                                <span class="text-2xl font-extrabold text-white">M</span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                        <div class="flex-1">
                                            <input type="file" name="logo_light" accept="image/*" class="settings-input text-xs">
                                            <p class="settings-hint"><?php echo e(__('PNG, SVG, or JPG. Recommended: 200x60px.')); ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label class="settings-label"><?php echo e(__('Logo (Dark Mode)')); ?></label>
                                    <div class="flex items-center gap-4">
                                        <div class="w-16 h-16 bg-gray-800 rounded-2xl flex items-center justify-center shadow-lg border border-border/30 overflow-hidden">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($s('logo_dark')): ?>
                                                <img src="<?php echo e(asset('storage/' . $s('logo_dark'))); ?>" alt="Logo Dark" class="h-12 w-12 object-contain">
                                            <?php else: ?>
                                                <span class="text-2xl font-extrabold text-white">M</span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                        <div class="flex-1">
                                            <input type="file" name="logo_dark" accept="image/*" class="settings-input text-xs">
                                            <p class="settings-hint"><?php echo e(__('PNG, SVG, or JPG for dark backgrounds.')); ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label class="settings-label"><?php echo e(__('Favicon')); ?></label>
                                    <div class="flex items-center gap-4">
                                        <div class="w-16 h-16 bg-surface border-2 border-dashed border-border rounded-2xl flex items-center justify-center overflow-hidden">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($s('favicon')): ?>
                                                <img src="<?php echo e(asset('storage/' . $s('favicon'))); ?>" alt="Favicon" class="h-8 w-8 object-contain">
                                            <?php else: ?>
                                                <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center">
                                                    <span class="text-xs font-extrabold text-white">M</span>
                                                </div>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                        <div class="flex-1">
                                            <input type="file" name="favicon" accept="image/*,.ico" class="settings-input text-xs">
                                            <p class="settings-hint"><?php echo e(__('ICO, PNG, or SVG. 32x32 or 64x64.')); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit" class="settings-save-btn">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <?php echo e(__('Save Branding')); ?>

                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        
        <div x-show="activeTab === 'email'" x-transition style="display: none;">
            <form method="POST" action="<?php echo e(route('admin.settings.update')); ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="_settings_group" value="email">
                <div class="panel overflow-hidden">
                    <div class="px-6 py-4 border-b border-border/50 bg-surface/50">
                        <h2 class="text-lg font-bold text-ink"><?php echo e(__('Email / SMTP Configuration')); ?></h2>
                        <p class="text-sm text-muted mt-0.5"><?php echo e(__('Default SMTP settings for system emails (verification, notifications, password resets).')); ?></p>
                    </div>
                    <div class="p-6 space-y-5">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                            <div>
                                <label class="settings-label"><?php echo e(__('Mail Driver')); ?></label>
                                <select name="mail_driver" class="settings-select">
                                    <?php $driver = $s('mail_driver', 'smtp'); ?>
                                    <option value="smtp" <?php echo e($driver === 'smtp' ? 'selected' : ''); ?>>SMTP</option>
                                    <option value="sendmail" <?php echo e($driver === 'sendmail' ? 'selected' : ''); ?>>Sendmail</option>
                                    <option value="log" <?php echo e($driver === 'log' ? 'selected' : ''); ?>><?php echo e(__('Log (testing)')); ?></option>
                                </select>
                            </div>
                            <div>
                                <label class="settings-label"><?php echo e(__('From Name')); ?></label>
                                <input type="text" name="smtp_from_name" value="<?php echo e($s('smtp_from_name', $s('mail_from_name', config('app.name')))); ?>" class="settings-input">
                            </div>
                            <div>
                                <label class="settings-label"><?php echo e(__('From Address')); ?></label>
                                <input type="email" name="smtp_from_address" value="<?php echo e($s('smtp_from_address', $s('mail_from_email', 'noreply@mailtrixy.com'))); ?>" class="settings-input">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                            <div>
                                <label class="settings-label"><?php echo e(__('SMTP Host')); ?></label>
                                <input type="text" name="smtp_host" value="<?php echo e($s('smtp_host')); ?>" placeholder="smtp.example.com" class="settings-input font-mono text-xs">
                            </div>
                            <div>
                                <label class="settings-label"><?php echo e(__('SMTP Port')); ?></label>
                                <input type="number" name="smtp_port" value="<?php echo e($s('smtp_port', '587')); ?>" class="settings-input font-mono">
                            </div>
                            <div>
                                <label class="settings-label"><?php echo e(__('Encryption')); ?></label>
                                <select name="smtp_encryption" class="settings-select">
                                    <?php $enc = $s('smtp_encryption', 'tls'); ?>
                                    <option value="tls" <?php echo e(strtolower($enc) === 'tls' ? 'selected' : ''); ?>>TLS</option>
                                    <option value="ssl" <?php echo e(strtolower($enc) === 'ssl' ? 'selected' : ''); ?>>SSL</option>
                                    <option value="none" <?php echo e(strtolower($enc) === 'none' ? 'selected' : ''); ?>>None</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                            <div>
                                <label class="settings-label"><?php echo e(__('SMTP Username')); ?></label>
                                <input type="text" name="smtp_username" value="<?php echo e($s('smtp_username')); ?>" placeholder="your-smtp-username" class="settings-input font-mono text-xs">
                            </div>
                            <div>
                                <label class="settings-label"><?php echo e(__('SMTP Password')); ?></label>
                                <input type="password" name="smtp_password" value="" placeholder="<?php echo e($s('smtp_password') ? '••••••••••••' : __('Enter SMTP password')); ?>" class="settings-input font-mono text-xs">
                                <p class="settings-hint"><?php echo e($s('smtp_password') ? __('Password is saved. Leave blank to keep current value.') : __('Enter your SMTP password.')); ?></p>
                            </div>
                        </div>
                        <div class="flex justify-end pt-2">
                            <button type="submit" class="settings-save-btn">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <?php echo e(__('Save Email Settings')); ?>

                            </button>
                        </div>
                    </div>
                </div>
            </form>

            
            <form method="POST" action="<?php echo e(route('admin.settings.test-smtp')); ?>" class="mt-6">
                <?php echo csrf_field(); ?>
                <div class="panel overflow-hidden">
                    <div class="px-6 py-4 border-b border-border/50 bg-gradient-to-r from-blue-50/80 to-indigo-50/80 dark:from-blue-950/30 dark:to-indigo-950/30">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/50 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-ink"><?php echo e(__('Test SMTP Connection')); ?></h2>
                                <p class="text-sm text-muted mt-0.5"><?php echo e(__('Send a test email to verify your configuration works correctly.')); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="flex flex-col sm:flex-row items-end gap-4">
                            <div class="flex-1 w-full">
                                <label class="settings-label"><?php echo e(__('Recipient Email Address')); ?></label>
                                <input type="email" name="test_email" value="<?php echo e(old('test_email', auth()->user()->email ?? '')); ?>" placeholder="test@example.com" required class="settings-input">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['test_email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="text-xs text-danger mt-1"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 shadow-lg transition-all whitespace-nowrap">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                <?php echo e(__('Send Test Email')); ?>

                            </button>
                        </div>
                        <p class="text-xs text-muted mt-3"><?php echo e(__('Save your SMTP settings above before sending a test email.')); ?></p>
                    </div>
                </div>
            </form>
        </div>

        
        <div x-show="activeTab === 'auth'" x-transition style="display: none;">
            <form method="POST" action="<?php echo e(route('admin.settings.update')); ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="_settings_group" value="auth">
                <div class="panel overflow-hidden">
                    <div class="px-6 py-4 border-b border-border/50 bg-surface/50">
                        <h2 class="text-lg font-bold text-ink"><?php echo e(__('Authentication Settings')); ?></h2>
                        <p class="text-sm text-muted mt-0.5"><?php echo e(__('Control how users register, verify, and access the platform.')); ?></p>
                    </div>
                    <div class="p-6 space-y-5">

                        <?php echo $__env->make('admin.settings._toggle', [
                            'name' => 'registration_enabled',
                            'label' => __('Enable Public Registration'),
                            'description' => __('Allow new users to create accounts on the platform'),
                            'value' => $s('registration_enabled', 'true'),
                            'color' => 'green',
                            'icon' => 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z',
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                        <?php echo $__env->make('admin.settings._toggle', [
                            'name' => 'social_login_enabled',
                            'label' => __('Enable Social Login'),
                            'description' => __('Allow users to sign in with Google, GitHub, or Microsoft'),
                            'value' => $s('social_login_enabled', 'true'),
                            'color' => 'blue',
                            'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                        <?php echo $__env->make('admin.settings._toggle', [
                            'name' => 'require_email_verification',
                            'label' => __('Require Email Verification'),
                            'description' => __('Users must verify their email before accessing the platform'),
                            'value' => $s('require_email_verification', 'true'),
                            'color' => 'indigo',
                            'icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                            <div>
                                <label class="settings-label"><?php echo e(__('Default Plan for New Users')); ?></label>
                                <select name="default_plan" class="settings-select">
                                    <?php $dp = $s('default_plan', 'free'); ?>
                                    <option value="__free_unlimited__" <?php if(\App\Support\BillingPolicy::isFree()): echo 'selected'; endif; ?>><?php echo e(__('Free — unlimited for everyone')); ?></option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                    <option value="<?php echo e($plan->slug); ?>" <?php echo e(strtolower($dp) === strtolower($plan->slug) ? 'selected' : ''); ?>><?php echo e($plan->name); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($plan->isFree()): ?> — <?php echo e(__('plan limits apply')); ?><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </select>
                                <p class="settings-hint"><?php echo e(__('A plan is assigned to new users. Free — unlimited enables all features without plan limits for everyone and hides billing. Cancel provider subscriptions first.')); ?></p>
                            </div>
                            <div>
                                <label class="settings-label"><?php echo e(__('Trial Period (Days)')); ?></label>
                                <input type="number" name="trial_days" value="<?php echo e($s('trial_days', '14')); ?>" min="0" max="365" class="settings-input">
                                <p class="settings-hint"><?php echo e(__('Set to 0 to disable trials. Users start on the default plan immediately.')); ?></p>
                            </div>
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit" class="settings-save-btn">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <?php echo e(__('Save Authentication Settings')); ?>

                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        
        <div x-show="activeTab === 'security'" x-transition style="display: none;">
            <form method="POST" action="<?php echo e(route('admin.settings.update')); ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="_settings_group" value="security">
                <div class="panel overflow-hidden">
                    <div class="px-6 py-4 border-b border-border/50 bg-surface/50">
                        <h2 class="text-lg font-bold text-ink"><?php echo e(__('Security Settings')); ?></h2>
                        <p class="text-sm text-muted mt-0.5"><?php echo e(__('Configure login limits, session policies, 2FA enforcement, and CAPTCHA.')); ?></p>
                    </div>
                    <div class="p-6 space-y-6">

                        <?php echo $__env->make('admin.settings._toggle', [
                            'name' => 'require_2fa_admins',
                            'label' => __('Force 2FA for Admins'),
                            'description' => __('Require all admin users to enable two-factor authentication'),
                            'value' => $s('require_2fa_admins', 'false'),
                            'color' => 'violet',
                            'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                            <div>
                                <label class="settings-label"><?php echo e(__('Max Login Attempts')); ?></label>
                                <input type="number" name="max_login_attempts" value="<?php echo e($s('max_login_attempts', '5')); ?>" min="1" max="20" class="settings-input">
                                <p class="settings-hint"><?php echo e(__('Number of failed attempts before account lockout.')); ?></p>
                            </div>
                            <div>
                                <label class="settings-label"><?php echo e(__('Lockout Duration (Minutes)')); ?></label>
                                <input type="number" name="lockout_duration" value="<?php echo e($s('lockout_duration', '15')); ?>" min="1" max="1440" class="settings-input">
                                <p class="settings-hint"><?php echo e(__('How long the account stays locked after exceeding attempts.')); ?></p>
                            </div>
                            <div>
                                <label class="settings-label"><?php echo e(__('Session Lifetime (Minutes)')); ?></label>
                                <input type="number" name="session_lifetime" value="<?php echo e($s('session_lifetime', '120')); ?>" min="5" max="10080" class="settings-input">
                                <p class="settings-hint"><?php echo e(__('Default: 120 min (2 hours). Max: 10080 min (7 days).')); ?></p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <a href="<?php echo e(route('admin.security-settings.index', ['tab' => 'password'])); ?>" class="text-brand underline"><?php echo e(__('Manage password and username rules')); ?></a>
                                <p class="settings-hint"><?php echo e(__('Shared by website and mobile registration, password changes and recovery.')); ?></p>
                            </div>
                        </div>

                        <div class="border-t border-border/50"></div>

                        <div>
                            <h3 class="text-sm font-bold text-ink uppercase tracking-wider mb-4 flex items-center gap-2">
                                <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                <?php echo e(__('hCaptcha Configuration')); ?>

                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="settings-label"><?php echo e(__('hCaptcha Site Key')); ?></label>
                                    <input type="text" name="hcaptcha_site_key" value="<?php echo e($s('hcaptcha_site_key')); ?>" class="settings-input font-mono text-xs" placeholder="10000000-ffff-ffff-ffff-000000000001">
                                </div>
                                <div>
                                    <label class="settings-label"><?php echo e(__('hCaptcha Secret Key')); ?></label>
                                    <input type="password" name="hcaptcha_secret_key" value="" placeholder="<?php echo e($s('hcaptcha_secret_key') ? '••••••••••••' : __('Enter hCaptcha secret')); ?>" class="settings-input font-mono text-xs">
                                    <p class="settings-hint"><?php echo e($s('hcaptcha_secret_key') ? __('Secret saved. Leave blank to keep.') : ''); ?> <?php echo e(__('Get keys at')); ?> <a href="https://www.hcaptcha.com/" target="_blank" class="text-brand hover:underline">hcaptcha.com</a>.</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit" class="settings-save-btn">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <?php echo e(__('Save Security Settings')); ?>

                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        
        

        
        <div x-show="activeTab === 'integrations'" x-transition style="display: none;">
            <form method="POST" action="<?php echo e(route('admin.settings.update')); ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="_settings_group" value="integrations">
                <div class="panel overflow-hidden">
                    <div class="px-6 py-4 border-b border-border/50 bg-surface/50">
                        <h2 class="text-lg font-bold text-ink"><?php echo e(__('OAuth & Payment Credentials')); ?></h2>
                        <p class="text-xs text-muted mt-1"><?php echo e(__('Configure OAuth app credentials for social login, email providers, and payment gateways. Users connect via OAuth — they don\'t see these keys.')); ?></p>
                    </div>
                    <div class="p-6 space-y-8">

                        
                        <?php echo $__env->make('admin.settings._integration', [
                            'id' => 'google',
                            'title' => __('Google (OAuth, Gmail, Calendar)'),
                            'enabled_key' => 'integration_google_enabled',
                            'fields' => [
                                ['name' => 'google_client_id', 'label' => __('Client ID'), 'type' => 'text', 'placeholder' => 'xxxx.apps.googleusercontent.com'],
                                ['name' => 'google_client_secret', 'label' => __('Client Secret'), 'type' => 'password', 'placeholder' => __('Enter client secret')],
                            ],
                            'help' => __('Create at') . ' <a href="https://console.cloud.google.com/apis/credentials" target="_blank" class="text-brand hover:underline">Google Cloud Console</a>.',
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                        <div class="border-t border-border/40"></div>

                        
                        <?php echo $__env->make('admin.settings._integration', [
                            'id' => 'microsoft',
                            'title' => __('Microsoft (Outlook, OneDrive)'),
                            'enabled_key' => 'integration_microsoft_enabled',
                            'fields' => [
                                ['name' => 'microsoft_client_id', 'label' => __('Client ID'), 'type' => 'text', 'placeholder' => __('Azure App Client ID')],
                                ['name' => 'microsoft_client_secret', 'label' => __('Client Secret'), 'type' => 'password', 'placeholder' => __('Enter client secret')],
                            ],
                            'help' => __('Create at') . ' <a href="https://portal.azure.com/#blade/Microsoft_AAD_RegisteredApps" target="_blank" class="text-brand hover:underline">Azure Portal</a>.',
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                        <div class="border-t border-border/40"></div>

                        
                        <?php echo $__env->make('admin.settings._integration', [
                            'id' => 'github',
                            'title' => __('GitHub (OAuth)'),
                            'enabled_key' => 'integration_github_enabled',
                            'fields' => [
                                ['name' => 'github_client_id', 'label' => __('Client ID'), 'type' => 'text', 'placeholder' => __('GitHub OAuth App Client ID')],
                                ['name' => 'github_client_secret', 'label' => __('Client Secret'), 'type' => 'password', 'placeholder' => __('Enter client secret')],
                            ],
                            'help' => __('Create at') . ' <a href="https://github.com/settings/developers" target="_blank" class="text-brand hover:underline">GitHub Developer Settings</a>.',
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                        <div class="border-t border-border/40"></div>

                        
                        <?php echo $__env->make('admin.settings._integration', [
                            'id' => 'stripe',
                            'title' => __('Stripe (Payments)'),
                            'enabled_key' => 'integration_stripe_enabled',
                            'fields' => [
                                ['name' => 'stripe_key', 'label' => __('Publishable Key'), 'type' => 'text', 'placeholder' => 'pk_live_...'],
                                ['name' => 'stripe_secret', 'label' => __('Secret Key'), 'type' => 'password', 'placeholder' => 'sk_live_...'],
                                ['name' => 'stripe_webhook_secret', 'label' => __('Webhook Secret'), 'type' => 'password', 'placeholder' => 'whsec_...'],
                            ],
                            'help' => __('Get from') . ' <a href="https://dashboard.stripe.com/apikeys" target="_blank" class="text-brand hover:underline">Stripe Dashboard</a>.',
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                        <div class="border-t border-border/40"></div>

                        
                        <?php echo $__env->make('admin.settings._integration', [
                            'id' => 'slack',
                            'title' => __('Slack (OAuth)'),
                            'enabled_key' => 'integration_slack_enabled',
                            'fields' => [
                                ['name' => 'slack_client_id', 'label' => __('Client ID'), 'type' => 'text', 'placeholder' => __('Slack App Client ID')],
                                ['name' => 'slack_client_secret', 'label' => __('Client Secret'), 'type' => 'password', 'placeholder' => __('Enter client secret')],
                                ['name' => 'slack_signing_secret', 'label' => __('Signing Secret'), 'type' => 'password', 'placeholder' => __('Enter signing secret')],
                            ],
                            'help' => __('Create at') . ' <a href="https://api.slack.com/apps" target="_blank" class="text-brand hover:underline">Slack API Dashboard</a>.',
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                        <div class="border-t border-border/40"></div>

                        
                        <?php echo $__env->make('admin.settings._integration', [
                            'id' => 'salesforce',
                            'title' => __('Salesforce (OAuth)'),
                            'enabled_key' => 'integration_salesforce_enabled',
                            'fields' => [
                                ['name' => 'salesforce_client_id', 'label' => __('Consumer Key'), 'type' => 'text', 'placeholder' => __('Connected App Consumer Key')],
                                ['name' => 'salesforce_client_secret', 'label' => __('Consumer Secret'), 'type' => 'password', 'placeholder' => __('Enter consumer secret')],
                            ],
                            'help' => __('Create a Connected App in') . ' <a href="https://login.salesforce.com/" target="_blank" class="text-brand hover:underline">Salesforce Setup</a>.',
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                        <div class="border-t border-border/40"></div>

                        
                        <?php echo $__env->make('admin.settings._integration', [
                            'id' => 'pusher',
                            'title' => __('Pusher (Real-time)'),
                            'enabled_key' => 'integration_pusher_enabled',
                            'fields' => [
                                ['name' => 'pusher_app_id', 'label' => __('App ID'), 'type' => 'text', 'placeholder' => __('Pusher App ID')],
                                ['name' => 'pusher_key', 'label' => __('Key'), 'type' => 'text', 'placeholder' => __('Pusher Key')],
                                ['name' => 'pusher_secret', 'label' => __('Secret'), 'type' => 'password', 'placeholder' => __('Enter secret')],
                                ['name' => 'pusher_cluster', 'label' => __('Cluster'), 'type' => 'text', 'placeholder' => 'us2'],
                            ],
                            'help' => __('Get from') . ' <a href="https://dashboard.pusher.com/" target="_blank" class="text-brand hover:underline">Pusher Dashboard</a>.',
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                        
                        <div class="rounded-xl border border-border/60 bg-surface/70 p-4 text-sm text-muted">
                            <p class="font-semibold text-ink mb-1"><?php echo e(__('How it works')); ?></p>
                            <ul class="list-disc list-inside space-y-1 text-xs">
                                <li><?php echo __('OAuth credentials here enable <strong>social login</strong> (Google, Microsoft, GitHub) and <strong>email account connections</strong>'); ?></li>
                                <li><?php echo __('Stripe credentials enable <strong>payment processing</strong> for subscriptions'); ?></li>
                                <li><?php echo __('Pusher credentials enable <strong>real-time notifications</strong>'); ?></li>
                                <li><?php echo __('Channel integrations (WhatsApp, SMS, Telegram) are configured by users in <strong>Settings > Channels</strong>'); ?></li>
                                <li><?php echo __('AI API keys are configured by users in <strong>Settings > AI Configuration</strong>'); ?></li>
                            </ul>
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit" class="settings-save-btn">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <?php echo e(__('Save Integrations')); ?>

                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        
        <div x-show="activeTab === 'legal'" x-transition style="display: none;">
            <form method="POST" action="<?php echo e(route('admin.settings.update')); ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="_settings_group" value="legal">
                <div class="panel overflow-hidden">
                    <div class="px-6 py-4 border-b border-border/50 bg-surface/50">
                        <h2 class="text-lg font-bold text-ink"><?php echo e(__('Legal / GDPR Settings')); ?></h2>
                        <p class="text-sm text-muted mt-0.5"><?php echo e(__('Configure cookie consent, data export, account deletion, and legal page links.')); ?></p>
                    </div>
                    <div class="p-6 space-y-6">

                        
                        <div>
                            <h3 class="text-sm font-bold text-ink uppercase tracking-wider mb-4 flex items-center gap-2">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                <?php echo e(__('Cookie Consent')); ?>

                            </h3>

                            <?php echo $__env->make('admin.settings._toggle', [
                                'name' => 'cookie_consent_enabled',
                                'label' => __('Enable Cookie Banner'),
                                'description' => __('Show a cookie consent popup to new visitors for GDPR compliance'),
                                'value' => $s('cookie_consent_enabled', $s('gdpr_cookie_enabled', 'true')),
                                'color' => 'indigo',
                                'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                            <div class="mt-5">
                                <label class="settings-label"><?php echo e(__('Cookie Banner Message')); ?></label>
                                <textarea rows="3" name="cookie_consent_message" class="settings-input resize-none"><?php echo e($s('cookie_consent_message', $s('gdpr_cookie_message', 'We use cookies to improve your experience. By continuing to browse, you agree to our use of cookies.'))); ?></textarea>
                            </div>
                        </div>

                        <div class="border-t border-border/50"></div>

                        
                        <div>
                            <h3 class="text-sm font-bold text-ink uppercase tracking-wider mb-4 flex items-center gap-2">
                                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <?php echo e(__('Data Rights')); ?>

                            </h3>

                            <?php echo $__env->make('admin.settings._toggle', [
                                'name' => 'gdpr_data_export_enabled',
                                'label' => __('Enable GDPR Data Export'),
                                'description' => __('Allow users to download all their personal data (GDPR Article 20)'),
                                'value' => $s('gdpr_data_export_enabled', 'true'),
                                'color' => 'blue',
                                'icon' => 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4',
                            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                            <div class="mt-4">
                                <?php echo $__env->make('admin.settings._toggle', [
                                    'name' => 'gdpr_account_deletion_enabled',
                                    'label' => __('Enable Account Self-Deletion'),
                                    'description' => __('Allow users to permanently delete their account and all associated data (GDPR Article 17)'),
                                    'value' => $s('gdpr_account_deletion_enabled', 'true'),
                                    'color' => 'red',
                                    'icon' => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16',
                                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            </div>
                        </div>

                        <div class="border-t border-border/50"></div>

                        
                        <div>
                            <h3 class="text-sm font-bold text-ink uppercase tracking-wider mb-4 flex items-center gap-2">
                                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <?php echo e(__('Legal Pages')); ?>

                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="settings-label"><?php echo e(__('Terms of Service URL')); ?></label>
                                    <input type="url" name="terms_of_service_url" value="<?php echo e($s('terms_of_service_url')); ?>" class="settings-input" placeholder="https://yourdomain.com/terms">
                                    <p class="settings-hint"><?php echo e(__('Linked from registration page and site footer.')); ?></p>
                                </div>
                                <div>
                                    <label class="settings-label"><?php echo e(__('Privacy Policy URL')); ?></label>
                                    <input type="url" name="privacy_policy_url" value="<?php echo e($s('privacy_policy_url', $s('gdpr_cookie_policy_url'))); ?>" class="settings-input" placeholder="https://yourdomain.com/privacy">
                                    <p class="settings-hint"><?php echo e(__('Linked from cookie banner, registration, and footer.')); ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit" class="settings-save-btn">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <?php echo e(__('Save Legal Settings')); ?>

                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        
        <div x-show="activeTab === 'pwa'" x-transition style="display: none;">
            <form method="POST" action="<?php echo e(route('admin.settings.update')); ?>" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="_settings_group" value="pwa">
                <div class="panel overflow-hidden">
                    <div class="px-6 py-4 border-b border-border/50 bg-surface/50">
                        <h2 class="text-lg font-bold text-ink"><?php echo e(__('Progressive Web App (PWA)')); ?></h2>
                        <p class="text-sm text-muted mt-0.5"><?php echo e(__('Allow users to install your platform as a native-like app on mobile and desktop.')); ?></p>
                    </div>
                    <div class="p-6 space-y-6">

                        
                        <div class="flex items-center justify-between p-5 rounded-xl border border-border bg-surface/50" x-data="{ enabled: <?php echo e($s('pwa_enabled', 'false') === 'true' ? 'true' : 'false'); ?> }">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl flex items-center justify-center" :class="enabled ? 'bg-success/15 text-success' : 'bg-muted/10 text-muted'">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-ink"><?php echo e(__('Enable PWA')); ?></h3>
                                    <p class="text-xs text-muted mt-0.5"><?php echo e(__('Injects manifest, service worker, and install prompt into all pages.')); ?></p>
                                </div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="pwa_enabled" value="false">
                                <input type="checkbox" name="pwa_enabled" value="true" x-model="enabled" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand"></div>
                            </label>
                        </div>

                        <div class="p-4 bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800/30 rounded-xl">
                            <div class="flex gap-3">
                                <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <p class="text-xs text-amber-700 dark:text-amber-400"><?php echo __('<strong>Requirement:</strong> PWA only works on sites served over <strong>HTTPS</strong>. If your domain does not have an SSL certificate, the install prompt will not appear.'); ?></p>
                            </div>
                        </div>

                        <div class="border-t border-border/50"></div>

                        
                        <div>
                            <h3 class="text-sm font-bold text-ink uppercase tracking-wider mb-4"><?php echo e(__('App Identity')); ?></h3>
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                                <div>
                                    <label class="settings-label"><?php echo e(__('App Name')); ?></label>
                                    <input type="text" name="pwa_app_name" value="<?php echo e($s('pwa_app_name', $s('site_name', 'MailTrixy'))); ?>" class="settings-input" placeholder="MailTrixy">
                                    <p class="settings-hint"><?php echo e(__('Shown on the home screen and in the task switcher.')); ?></p>
                                </div>
                                <div>
                                    <label class="settings-label"><?php echo e(__('Short Name')); ?></label>
                                    <input type="text" name="pwa_short_name" value="<?php echo e($s('pwa_short_name', $s('site_name', 'MailTrixy'))); ?>" class="settings-input" placeholder="MailTrixy">
                                    <p class="settings-hint"><?php echo e(__('Used when space is limited (e.g., under home screen icon). Max 12 chars.')); ?></p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <label class="settings-label"><?php echo e(__('Start URL')); ?></label>
                                <input type="text" name="pwa_start_url" value="<?php echo e($s('pwa_start_url', '/dashboard')); ?>" class="settings-input" placeholder="/dashboard">
                                <p class="settings-hint"><?php echo __('The page that opens when the installed app is launched. Use <code class="text-xs">/dashboard</code> or <code class="text-xs">/</code>.'); ?></p>
                            </div>
                        </div>

                        <div class="border-t border-border/50"></div>

                        
                        <div>
                            <h3 class="text-sm font-bold text-ink uppercase tracking-wider mb-4"><?php echo e(__('Icons & Splash')); ?></h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="settings-label"><?php echo e(__('PWA Icon')); ?></label>
                                    <div class="flex items-center gap-4">
                                        <div class="w-20 h-20 bg-surface border-2 border-dashed border-border rounded-2xl flex items-center justify-center overflow-hidden">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($s('pwa_icon')): ?>
                                                <img src="<?php echo e(asset('storage/' . $s('pwa_icon'))); ?>" alt="PWA Icon" class="h-16 w-16 object-contain">
                                            <?php else: ?>
                                                <div class="w-14 h-14 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center">
                                                    <span class="text-xl font-extrabold text-white">M</span>
                                                </div>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                        <div class="flex-1">
                                            <input type="file" name="pwa_icon" accept="image/png,image/jpg,image/jpeg" class="settings-input text-xs">
                                            <p class="settings-hint"><?php echo e(__('512 x 512 px PNG recommended. Square with solid background.')); ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label class="settings-label"><?php echo e(__('Splash Screen')); ?> <span class="text-muted font-normal"><?php echo e(__('(iOS only)')); ?></span></label>
                                    <div class="flex items-center gap-4">
                                        <div class="w-20 h-20 bg-surface border-2 border-dashed border-border rounded-2xl flex items-center justify-center overflow-hidden">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($s('pwa_splash')): ?>
                                                <img src="<?php echo e(asset('storage/' . $s('pwa_splash'))); ?>" alt="Splash" class="h-16 w-16 object-cover">
                                            <?php else: ?>
                                                <svg class="w-8 h-8 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                        <div class="flex-1">
                                            <input type="file" name="pwa_splash" accept="image/png,image/jpg,image/jpeg" class="settings-input text-xs">
                                            <p class="settings-hint"><?php echo e(__('2048 x 2732 px PNG/JPG. Shown while the app loads on Apple devices.')); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-border/50"></div>

                        
                        <div>
                            <h3 class="text-sm font-bold text-ink uppercase tracking-wider mb-4"><?php echo e(__('Colors')); ?></h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div x-data="{ hex: '<?php echo e($s('pwa_theme_color', '#6366f1')); ?>' }">
                                    <label class="settings-label"><?php echo e(__('Theme Color')); ?></label>
                                    <div class="flex items-center gap-3">
                                        <input type="color" name="pwa_theme_color" x-model="hex" class="w-12 h-12 rounded-xl border border-border cursor-pointer p-1">
                                        <input type="text" x-model="hex" class="flex-1 settings-input font-mono text-xs" maxlength="7" readonly>
                                    </div>
                                    <p class="settings-hint"><?php echo e(__('Browser toolbar color on Android and title bar on desktop.')); ?></p>
                                </div>
                                <div x-data="{ hex: '<?php echo e($s('pwa_background_color', '#0f1117')); ?>' }">
                                    <label class="settings-label"><?php echo e(__('Background Color')); ?></label>
                                    <div class="flex items-center gap-3">
                                        <input type="color" name="pwa_background_color" x-model="hex" class="w-12 h-12 rounded-xl border border-border cursor-pointer p-1">
                                        <input type="text" x-model="hex" class="flex-1 settings-input font-mono text-xs" maxlength="7" readonly>
                                    </div>
                                    <p class="settings-hint"><?php echo e(__('Splash screen background. Match your site\'s background for a seamless launch.')); ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-border/50"></div>

                        
                        <div class="p-5 rounded-xl bg-brand/5 border border-brand/10">
                            <h4 class="text-sm font-bold text-ink mb-2"><?php echo e(__('How It Works')); ?></h4>
                            <ol class="list-decimal list-inside text-xs text-muted space-y-1.5">
                                <li><?php echo e(__('Enable PWA above and configure the app name, icon, and colors.')); ?></li>
                                <li><?php echo __('The system injects a <code class="text-xs bg-surface px-1 rounded">&lt;link rel="manifest"&gt;</code> tag and registers a service worker on all frontend pages.'); ?></li>
                                <li><?php echo __('The manifest is served dynamically from <code class="text-xs bg-surface px-1 rounded">/manifest.json</code> using your saved settings.'); ?></li>
                                <li><?php echo e(__('Users will see a browser-native install prompt (Android/Chrome/Edge) or can use Share > Add to Home Screen (iOS/Safari).')); ?></li>
                            </ol>
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit" class="settings-save-btn">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <?php echo e(__('Save PWA Settings')); ?>

                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        
        <div x-show="activeTab === 'custom_code'" x-transition style="display: none;">
            <form method="POST" action="<?php echo e(route('admin.settings.update')); ?>" class="space-y-6">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="_settings_group" value="custom_code">

                <div class="panel p-6 space-y-5">
                    <div>
                        <p class="panel-heading"><?php echo e(__('Custom CSS / JS / Head & Footer Code')); ?></p>
                        <p class="text-sm text-muted mt-1">
                            <?php echo e(__('Inject custom code site-wide. Useful for analytics tags, chat widgets, custom styling tweaks, and third-party integrations. Code is rendered raw — no escaping — so use carefully.')); ?>

                        </p>
                    </div>

                    
                    <div>
                        <label class="block text-sm font-medium text-ink/80 mb-1"><?php echo e(__('Custom CSS')); ?></label>
                        <textarea name="custom_css" rows="8"
                                  class="settings-input font-mono text-xs"
                                  placeholder="/* example */&#10;.btn-primary { letter-spacing: 0.02em; }&#10;.site-header .logo { filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3)); }"><?php echo e($s('custom_css', '')); ?></textarea>
                        <p class="text-xs text-muted mt-1">
                            <?php echo e(__('Wrapped in')); ?> <code>&lt;style&gt;</code> <?php echo e(__('and rendered into the')); ?> <code>&lt;head&gt;</code> <?php echo e(__('of every page.')); ?>

                        </p>
                    </div>

                    
                    <div>
                        <label class="block text-sm font-medium text-ink/80 mb-1"><?php echo e(__('Head Code')); ?> <span class="text-muted">(<code>&lt;head&gt;</code>)</span></label>
                        <textarea name="head_code" rows="6"
                                  class="settings-input font-mono text-xs"
                                  placeholder="<!-- Google Analytics -->&#10;<script async src='https://www.googletagmanager.com/gtag/js?id=G-XXXXXX'></script>&#10;<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','G-XXXXXX');</script>"><?php echo e($s('head_code', '')); ?></textarea>
                        <p class="text-xs text-muted mt-1">
                            <?php echo e(__('Raw HTML rendered inside')); ?> <code>&lt;head&gt;</code>. <?php echo e(__('Use for analytics tags, custom meta tags, or third-party scripts that must load early.')); ?>

                        </p>
                    </div>

                    
                    <div>
                        <label class="block text-sm font-medium text-ink/80 mb-1"><?php echo e(__('Footer Code')); ?> <span class="text-muted">(<?php echo e(__('before')); ?> <code>&lt;/body&gt;</code>)</span></label>
                        <textarea name="footer_code" rows="6"
                                  class="settings-input font-mono text-xs"
                                  placeholder="<!-- Chat widget / pixel / late-loading scripts -->&#10;<script>(function(){ var w=document.createElement('script'); w.src='https://example.com/widget.js'; w.async=true; document.body.appendChild(w); })();</script>"><?php echo e($s('footer_code', '')); ?></textarea>
                        <p class="text-xs text-muted mt-1">
                            <?php echo e(__('Raw HTML rendered just before')); ?> <code>&lt;/body&gt;</code>. <?php echo e(__('Use for chat widgets, late-loading scripts, conversion pixels.')); ?>

                        </p>
                    </div>

                    <div class="flex items-start gap-3 p-4 bg-warning/5 border border-warning/20 rounded-xl">
                        <svg class="w-5 h-5 text-warning flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <div class="text-xs text-warning">
                            <p class="font-semibold"><?php echo e(__('Heads up')); ?></p>
                            <p class="mt-1">
                                <?php echo e(__('Code here is rendered exactly as written — no escaping. A broken')); ?> <code>&lt;script&gt;</code> <?php echo e(__('tag can break the entire site. Test in a staging environment first when possible.')); ?>

                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="btn-primary"><?php echo e(__('Save Custom Code')); ?></button>
                </div>
            </form>
        </div>

        
        <div x-show="activeTab === 'maintenance'" x-transition style="display: none;">
            
            <?php
                $isDown = app()->isDownForMaintenance();
                $bypassUrl = session('maintenance_bypass_url');
                $storedSecret = \App\Models\SystemSetting::get('maintenance_secret', '');
                $storedBypassUrl = ($isDown && $storedSecret) ? rtrim(config('app.url'), '/') . '/' . $storedSecret : null;
                $showBypassUrl = $bypassUrl ?: $storedBypassUrl;
            ?>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showBypassUrl): ?>
            <div class="panel overflow-hidden mb-6 border-2 border-warning/40 bg-warning/5">
                <div class="px-6 py-5">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 bg-warning/15 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-base font-bold text-ink mb-1"><?php echo e(__('Your admin bypass URL — bookmark this NOW')); ?></h3>
                            <p class="text-sm text-muted mb-3"><?php echo e(__('Open this URL in any browser to bypass the maintenance page and reach the admin panel. Without it you cannot get back in once you log out.')); ?></p>
                            <div class="flex items-center gap-2 p-3 bg-surface rounded-xl border border-border/60">
                                <code class="flex-1 text-sm font-mono text-ink truncate select-all" id="bypass-url"><?php echo e($showBypassUrl); ?></code>
                                <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('bypass-url').textContent); this.innerText='Copied'; setTimeout(()=>this.innerText='Copy',1500);"
                                        class="px-3 py-1.5 text-xs font-bold bg-brand text-white rounded-lg hover:bg-brand-strong transition-colors flex-shrink-0">Copy</button>
                                <a href="<?php echo e($showBypassUrl); ?>" target="_blank"
                                   class="px-3 py-1.5 text-xs font-bold border border-border text-ink rounded-lg hover:bg-surface-2 transition-colors flex-shrink-0"><?php echo e(__('Open')); ?></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="panel overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-border/50 bg-surface/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-bold text-ink"><?php echo e(__('Application Status')); ?></h2>
                            <p class="text-sm text-muted mt-0.5"><?php echo e(__('Toggle real maintenance mode using Laravel\'s built-in artisan commands.')); ?></p>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isDown): ?>
                            <span class="inline-flex items-center gap-2 px-4 py-2 text-xs font-bold text-danger bg-danger/15 border border-danger/20 rounded-xl">
                                <span class="w-2.5 h-2.5 bg-red-500 rounded-full animate-pulse"></span>
                                <?php echo e(__('Maintenance Active')); ?>

                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center gap-2 px-4 py-2 text-xs font-bold text-success bg-success/15 border border-success/20 rounded-xl">
                                <span class="w-2.5 h-2.5 bg-green-500 rounded-full animate-pulse"></span>
                                <?php echo e(__('Site is Live')); ?>

                            </span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
                <div class="p-6">
                    <div class="flex items-center justify-between p-5 rounded-xl <?php echo e($isDown ? 'bg-gradient-to-r from-red-50 to-orange-50 dark:from-red-950/30 dark:to-orange-950/30 border border-danger/20' : 'bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-950/30 dark:to-emerald-950/30 border border-success/20'); ?>">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 <?php echo e($isDown ? 'bg-danger/15' : 'bg-success/15'); ?> rounded-2xl flex items-center justify-center">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isDown): ?>
                                    <svg class="w-7 h-7 text-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <?php else: ?>
                                    <svg class="w-7 h-7 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-ink"><?php echo e($isDown ? __('Maintenance Mode is ON') : __('Application is Running')); ?></h3>
                                <p class="text-sm text-muted mt-0.5"><?php echo e($isDown ? __('All visitors see the maintenance page. Admins with the secret URL can bypass it.') : __('All users can access the platform normally.')); ?></p>
                            </div>
                        </div>
                        <form method="POST" action="<?php echo e(route('admin.settings.maintenance')); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit"
                                    x-data
                                    @click.prevent="if(confirm('<?php echo e($isDown ? __('Bring the site back online? All users will regain access.') : __('WARNING: This will immediately take the site offline for all users. Are you sure?')); ?>')) $el.closest('form').submit()"
                                    class="inline-flex items-center gap-2 px-6 py-3 text-sm font-bold text-white rounded-xl shadow-lg transition-all <?php echo e($isDown ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700'); ?>">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isDown): ?>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <?php echo e(__('Disable Maintenance')); ?>

                                <?php else: ?>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    <?php echo e(__('Enable Maintenance')); ?>

                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </button>
                        </form>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$isDown): ?>
                    <div class="mt-4 p-4 bg-warning/10 border border-warning/20 rounded-xl">
                        <div class="flex gap-3">
                            <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <p class="text-xs text-amber-700 dark:text-amber-400"><?php echo e(__('Enabling maintenance mode will immediately make the site unavailable to all users. A secret URL will be generated for admin access.')); ?></p>
                        </div>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            
            <form method="POST" action="<?php echo e(route('admin.settings.update')); ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="_settings_group" value="maintenance">
                <div class="panel overflow-hidden">
                    <div class="px-6 py-4 border-b border-border/50 bg-surface/50">
                        <h2 class="text-lg font-bold text-ink"><?php echo e(__('Maintenance Page Settings')); ?></h2>
                        <p class="text-sm text-muted mt-0.5"><?php echo e(__('Configure the message shown to visitors during maintenance.')); ?></p>
                    </div>
                    <div class="p-6 space-y-5">
                        <div>
                            <label class="settings-label"><?php echo e(__('Maintenance Message')); ?></label>
                            <textarea rows="4" name="maintenance_message" class="settings-input resize-none"><?php echo e($s('maintenance_message', "We're currently performing scheduled maintenance. We'll be back shortly.")); ?></textarea>
                        </div>
                        <div>
                            <label class="settings-label"><?php echo e(__('Allowed IPs (Bypass Maintenance)')); ?></label>
                            <input type="text" name="maintenance_allowed_ips" value="<?php echo e($s('maintenance_allowed_ips')); ?>" placeholder="127.0.0.1, 192.168.1.1" class="settings-input">
                            <p class="settings-hint"><?php echo e(__('Comma-separated list of IPs that can bypass maintenance mode.')); ?></p>
                        </div>
                        <div class="flex justify-end pt-2">
                            <button type="submit" class="settings-save-btn">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <?php echo e(__('Save Maintenance Settings')); ?>

                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

    </div>

    <script>
    function settingsPage() {
        return {
            activeTab: new URLSearchParams(window.location.search).get('tab') || 'general',
            init() {
                this.$watch('activeTab', (val) => {
                    const url = new URL(window.location);
                    url.searchParams.set('tab', val);
                    window.history.replaceState({}, '', url);
                });
            }
        };
    }

    // aiConfig() Alpine helper removed — the AI Config tab that used it was
    // removed in favor of /admin/ai-providers (single source of truth).
    </script>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc8c9fd5d7827a77a31381de67195f0c3)): ?>
<?php $attributes = $__attributesOriginalc8c9fd5d7827a77a31381de67195f0c3; ?>
<?php unset($__attributesOriginalc8c9fd5d7827a77a31381de67195f0c3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc8c9fd5d7827a77a31381de67195f0c3)): ?>
<?php $component = $__componentOriginalc8c9fd5d7827a77a31381de67195f0c3; ?>
<?php unset($__componentOriginalc8c9fd5d7827a77a31381de67195f0c3); ?>
<?php endif; ?>
<?php /**PATH /home/dahimail.com/public_html/resources/views/admin/settings.blade.php ENDPATH**/ ?>