<?php if (isset($component)) { $__componentOriginalc8c9fd5d7827a77a31381de67195f0c3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc8c9fd5d7827a77a31381de67195f0c3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.admin','data' => ['title' => __('Security Settings'),'subtitle' => __('Configure login security, password policies, sessions, and blocking.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.admin'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Security Settings')),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Configure login security, password policies, sessions, and blocking.'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <div class="space-y-6" x-data="{ activeTab: '<?php echo e(old('_settings_group', request('tab', 'login'))); ?>' }">

        
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
                    ['id' => 'login',    'label' => __('Login Security'),  'icon' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'],
                    ['id' => 'password', 'label' => __('Password & Username Rules'), 'icon' => 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z'],
                    ['id' => 'session',  'label' => __('Session'),         'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['id' => 'twofactor','label' => __('Two-Factor Auth'), 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                    ['id' => 'blocking', 'label' => __('IP / Location'),   'icon' => 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636'],
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
            $s = fn(string $key, string $default = '') => old($key, $settings[$key] ?? $default);
        ?>

        
        <div x-show="activeTab === 'login'" x-transition>
            <form method="POST" action="<?php echo e(route('admin.security-settings.update')); ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="_settings_group" value="login">
                <div class="panel overflow-hidden">
                    <div class="px-6 py-4 border-b border-border/50 bg-surface/50">
                        <h2 class="text-lg font-bold text-ink"><?php echo e(__('Login Security')); ?></h2>
                        <p class="text-sm text-muted mt-0.5"><?php echo e(__('Configure login attempt limits, lockout, and CAPTCHA protection.')); ?></p>
                    </div>
                    <div class="p-6 space-y-5">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                            <div>
                                <label for="max_login_attempts" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('Max Login Attempts')); ?></label>
                                <input type="number" name="max_login_attempts" id="max_login_attempts" value="<?php echo e($s('max_login_attempts', '5')); ?>" min="1" max="20"
                                       class="w-full px-4 py-2.5 text-sm border border-border rounded-xl focus:outline-none focus:ring-2 focus:ring-brand/40 focus:border-transparent bg-surface text-ink">
                                <p class="text-xs text-muted mt-1"><?php echo e(__('Number of failed attempts before account lockout.')); ?></p>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['max_login_attempts'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-danger text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div>
                                <label for="lockout_minutes" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('Lockout Duration (minutes)')); ?></label>
                                <input type="number" name="lockout_minutes" id="lockout_minutes" value="<?php echo e($s('lockout_minutes', '15')); ?>" min="1" max="1440"
                                       class="w-full px-4 py-2.5 text-sm border border-border rounded-xl focus:outline-none focus:ring-2 focus:ring-brand/40 focus:border-transparent bg-surface text-ink">
                                <p class="text-xs text-muted mt-1"><?php echo e(__('How long the account stays locked after too many attempts.')); ?></p>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['lockout_minutes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-danger text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>

                        <?php echo $__env->make('admin.settings._toggle', [
                            'name'        => 'captcha_enabled',
                            'label'       => 'CAPTCHA Protection',
                            'description' => 'Show CAPTCHA challenge on login after failed attempts.',
                            'value'       => $s('captcha_enabled', 'false'),
                            'color'       => 'green',
                            'icon'        => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                            <div>
                                <label for="captcha_site_key" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('CAPTCHA Site Key')); ?></label>
                                <input type="text" name="captcha_site_key" id="captcha_site_key" value="<?php echo e($s('captcha_site_key')); ?>"
                                       placeholder="6LeIxAcTAAAAAJcZV..."
                                       class="w-full px-4 py-2.5 text-sm border border-border rounded-xl focus:outline-none focus:ring-2 focus:ring-brand/40 focus:border-transparent bg-surface text-ink font-mono">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['captcha_site_key'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-danger text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div>
                                <label for="captcha_secret_key" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('CAPTCHA Secret Key')); ?></label>
                                <input type="password" name="captcha_secret_key" id="captcha_secret_key" value="<?php echo e($s('captcha_secret_key')); ?>"
                                       placeholder="6LeIxAcTAAAAAGG-v..."
                                       class="w-full px-4 py-2.5 text-sm border border-border rounded-xl focus:outline-none focus:ring-2 focus:ring-brand/40 focus:border-transparent bg-surface text-ink font-mono">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['captcha_secret_key'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-danger text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-brand text-white text-sm font-semibold rounded-xl hover:bg-brand-strong shadow-lg shadow-soft transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <?php echo e(__('Save Login Settings')); ?>

                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        
        <div x-show="activeTab === 'password'" x-transition style="display: none;">
            <form method="POST" action="<?php echo e(route('admin.security-settings.update')); ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="_settings_group" value="password">
                <div class="panel overflow-hidden">
                    <div class="px-6 py-4 border-b border-border/50 bg-surface/50">
                        <h2 class="text-lg font-bold text-ink"><?php echo e(__('Password & Username Rules')); ?></h2>
                        <p class="text-sm text-muted mt-0.5"><?php echo e(__('Apply these rules to new registrations and password changes or resets on the website and mobile app. Existing passwords keep working.')); ?></p>
                    </div>
                    <div class="p-6 space-y-5">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['password' => 'Password', 'username' => 'Username']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $prefix => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                            <h3 class="font-semibold"><?php echo e($label); ?></h3>
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['min' => 'Minimum length', 'max' => 'Maximum length']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $limit => $caption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                <?php ($key = $prefix.'_'.$limit.'_length'); ?>
                                <label class="block text-sm"><?php echo e($caption); ?>

                                    <input class="input" type="number" name="<?php echo e($key); ?>" value="<?php echo e($s($key)); ?>" min="<?php echo e($prefix === 'password' ? 6 : 1); ?>" max="<?php echo e($prefix === 'password' ? 72 : 64); ?>" required>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = [$key];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-danger"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </label>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </div>
                            <?php ($choices = $prefix === 'password' ? [
                                'require_uppercase' => 'Require an uppercase letter (A–Z)',
                                'require_lowercase' => 'Require a lowercase letter (a–z)',
                                'require_numbers' => 'Require a number',
                                'require_symbols' => 'Require a special character',
                            ] : [
                                'allow_uppercase' => 'Accept capital letters (saved as lowercase)',
                                'allow_numbers' => 'Allow numbers',
                                'allow_dots' => 'Allow dots',
                                'allow_dashes' => 'Allow dashes',
                                'allow_underscores' => 'Allow underscores',
                            ]); ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $choices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $suffix => $caption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                <?php ($key = $prefix.'_'.$suffix); ?>
                                <label class="flex items-center gap-3 text-sm">
                                    <input type="hidden" name="<?php echo e($key); ?>" value="0">
                                    <input type="checkbox" name="<?php echo e($key); ?>" value="1" <?php if(filter_var($s($key), FILTER_VALIDATE_BOOLEAN)): echo 'checked'; endif; ?>>
                                    <?php echo e($caption); ?>

                                </label>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = [$key];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-danger text-xs"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <p class="text-sm text-muted">Usernames are case-insensitive email addresses. Letters are always allowed; dots, dashes and underscores can only appear singly between letters or numbers. Passwords support up to 72 bytes (some characters use multiple bytes).</p>

                        <div class="flex justify-end pt-2">
                            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-brand text-white text-sm font-semibold rounded-xl hover:bg-brand-strong shadow-lg shadow-soft transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <?php echo e(__('Save Password Policy')); ?>

                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        
        <div x-show="activeTab === 'session'" x-transition style="display: none;">
            <form method="POST" action="<?php echo e(route('admin.security-settings.update')); ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="_settings_group" value="session">
                <div class="panel overflow-hidden">
                    <div class="px-6 py-4 border-b border-border/50 bg-surface/50">
                        <h2 class="text-lg font-bold text-ink"><?php echo e(__('Session Configuration')); ?></h2>
                        <p class="text-sm text-muted mt-0.5"><?php echo e(__('Control session lifetime and concurrent session behavior.')); ?></p>
                    </div>
                    <div class="p-6 space-y-5">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                            <div>
                                <label for="session_lifetime" class="block text-sm font-medium text-ink mb-1.5"><?php echo e(__('Session Lifetime (minutes)')); ?></label>
                                <input type="number" name="session_lifetime_minutes" id="session_lifetime_minutes" value="<?php echo e($s('session_lifetime_minutes', '120')); ?>" min="5" max="1440"
                                       class="w-full px-4 py-2.5 text-sm border border-border rounded-xl focus:outline-none focus:ring-2 focus:ring-brand/40 focus:border-transparent bg-surface text-ink">
                                <p class="text-xs text-muted mt-1"><?php echo e(__('How long a session stays active without activity.')); ?></p>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['session_lifetime'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-danger text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>

                        <?php echo $__env->make('admin.settings._toggle', [
                            'name'        => 'single_session',
                            'label'       => 'Single Session Only',
                            'description' => 'Allow only one active session per user. New logins will invalidate previous sessions.',
                            'value'       => $s('single_session', 'false'),
                            'color'       => 'green',
                            'icon'        => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                        <div class="flex justify-end pt-2">
                            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-brand text-white text-sm font-semibold rounded-xl hover:bg-brand-strong shadow-lg shadow-soft transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <?php echo e(__('Save Session Settings')); ?>

                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        
        <div x-show="activeTab === 'twofactor'" x-transition style="display: none;">
            <form method="POST" action="<?php echo e(route('admin.security-settings.update')); ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="_settings_group" value="twofactor">
                <div class="panel overflow-hidden">
                    <div class="px-6 py-4 border-b border-border/50 bg-surface/50">
                        <h2 class="text-lg font-bold text-ink"><?php echo e(__('Two-Factor Authentication')); ?></h2>
                        <p class="text-sm text-muted mt-0.5"><?php echo e(__('Configure two-factor authentication requirements.')); ?></p>
                    </div>
                    <div class="p-6 space-y-5">

                        <?php echo $__env->make('admin.settings._toggle', [
                            'name'        => 'two_factor_enabled',
                            'label'       => 'Enable Two-Factor Authentication',
                            'description' => 'Allow users to set up two-factor authentication for their accounts.',
                            'value'       => $s('two_factor_enabled', 'true'),
                            'color'       => 'green',
                            'icon'        => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                        <?php echo $__env->make('admin.settings._toggle', [
                            'name'        => 'two_factor_admin_only',
                            'label'       => 'Required for Admins Only',
                            'description' => 'Only require two-factor authentication for admin accounts.',
                            'value'       => $s('two_factor_admin_only', 'false'),
                            'color'       => 'indigo',
                            'icon'        => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z',
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                        <div class="flex justify-end pt-2">
                            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-brand text-white text-sm font-semibold rounded-xl hover:bg-brand-strong shadow-lg shadow-soft transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <?php echo e(__('Save 2FA Settings')); ?>

                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        
        <div x-show="activeTab === 'blocking'" x-transition style="display: none;">
            <form method="POST" action="<?php echo e(route('admin.security-settings.update')); ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="_settings_group" value="blocking">
                <div class="panel overflow-hidden">
                    <div class="px-6 py-4 border-b border-border/50 bg-surface/50">
                        <h2 class="text-lg font-bold text-ink"><?php echo e(__('IP & Location Blocking')); ?></h2>
                        <p class="text-sm text-muted mt-0.5"><?php echo e(__('Enable or disable IP and geographic location blocking.')); ?></p>
                    </div>
                    <div class="p-6 space-y-5">

                        <?php echo $__env->make('admin.settings._toggle', [
                            'name'        => 'ip_blocking_enabled',
                            'label'       => 'IP Blocking',
                            'description' => 'Block access from specific IP addresses. Manage blocked IPs from the Blocked IPs page.',
                            'value'       => $s('ip_blocking_enabled', 'true'),
                            'color'       => 'red',
                            'icon'        => 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636',
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                        <?php echo $__env->make('admin.settings._toggle', [
                            'name'        => 'location_blocking_enabled',
                            'label'       => 'Location Blocking',
                            'description' => 'Block access from specific countries, states, or cities. Manage from the Blocked Locations page.',
                            'value'       => $s('location_blocking_enabled', 'false'),
                            'color'       => 'red',
                            'icon'        => 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                        <div class="flex justify-end pt-2">
                            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-brand text-white text-sm font-semibold rounded-xl hover:bg-brand-strong shadow-lg shadow-soft transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <?php echo e(__('Save Blocking Settings')); ?>

                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
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
<?php /**PATH /home/dahimail.com/public_html/resources/views/admin/security-settings/index.blade.php ENDPATH**/ ?>