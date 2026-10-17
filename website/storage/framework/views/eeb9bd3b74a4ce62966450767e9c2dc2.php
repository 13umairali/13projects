<?php if (isset($component)) { $__componentOriginala9d35bca55314290701d0fd9db1a187a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala9d35bca55314290701d0fd9db1a187a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.settings','data' => ['title' => __('Account Settings')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.settings'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Account Settings'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <div class="space-y-6">
        
        <div>
            <h1 class="text-2xl font-bold text-ink"><?php echo e(__('Account Settings')); ?></h1>
            <p class="text-sm text-muted mt-1"><?php echo e(__('Manage your account preferences and configuration.')); ?></p>
        </div>

        
        <div class="bg-surface-2 rounded-2xl border border-border p-6">
            <h2 class="text-lg font-semibold text-ink mb-4"><?php echo e(__('Account Information')); ?></h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="p-4 bg-surface rounded-xl">
                    <p class="text-xs text-muted"><?php echo e(__('Account ID')); ?></p>
                    <p class="text-sm font-mono font-medium text-ink mt-0.5"><?php echo e(auth()->user()->id); ?></p>
                </div>
                <div class="p-4 bg-surface rounded-xl">
                    <p class="text-xs text-muted"><?php echo e(__('Account Created')); ?></p>
                    <p class="text-sm font-medium text-ink mt-0.5"><?php echo e(auth()->user()->created_at->format('F j, Y')); ?></p>
                </div>
                <div class="p-4 bg-surface rounded-xl">
                    <p class="text-xs text-muted"><?php echo e(__('Email')); ?></p>
                    <p class="text-sm font-medium text-ink mt-0.5"><?php echo e(auth()->user()->email ?? 'john@mailtrixy.com'); ?></p>
                </div>
                <div class="p-4 bg-surface rounded-xl">
                    <p class="text-xs text-muted"><?php echo e(__('Account Status')); ?></p>
                    <p class="text-sm font-medium text-ink mt-0.5"><?php echo e(ucfirst(auth()->user()->status ?? 'active')); ?></p>
                </div>
                <div class="p-4 bg-surface rounded-xl">
                    <p class="text-xs text-muted"><?php echo e(__('Auth Provider')); ?></p>
                    <p class="text-sm font-medium text-ink mt-0.5"><?php echo e(__('Email & Password')); ?></p>
                </div>
            </div>
        </div>

        

        
        <div class="bg-surface-2 rounded-2xl border border-border p-6">
            <h2 class="text-lg font-semibold text-ink mb-4"><?php echo e(__('Connected Accounts')); ?></h2>
            <div class="space-y-3">
                <?php
                $oauthProviders = [
                    ['name' => 'Google', 'connected' => false, 'bg' => 'bg-danger/10', 'text' => 'text-danger'],
                    ['name' => 'Microsoft', 'connected' => false, 'bg' => 'bg-info/10', 'text' => 'text-blue-600'],
                    ['name' => 'GitHub', 'connected' => false, 'bg' => 'bg-surface', 'text' => 'text-muted'],
                ];
                ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $oauthProviders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $provider): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                <div class="flex items-center justify-between p-4 bg-surface rounded-xl">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 <?php echo e($provider['bg']); ?> rounded-xl flex items-center justify-center">
                            <span class="text-sm font-bold <?php echo e($provider['text']); ?>"><?php echo e(substr($provider['name'], 0, 1)); ?></span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-ink"><?php echo e($provider['name']); ?></p>
                            <p class="text-xs text-muted"><?php echo e(__('Not connected')); ?></p>
                        </div>
                    </div>
                    <a href="<?php echo e(url('/auth/' . strtolower($provider['name']) . '/redirect')); ?>" class="px-4 py-2 text-sm font-medium text-muted bg-surface border border-border rounded-xl transition-colors inline-block cursor-default opacity-60" :title="__('Social login connections are managed automatically when you sign in with') . ' ' . $provider['name']">
                        <?php echo e(__('Auto-connected on login')); ?>

                    </a>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        </div>

        
        <div class="bg-surface-2 rounded-2xl border-2 border-danger/20 p-6" x-data="{ showDeactivate: false, showDelete: false }">
            <h2 class="text-lg font-semibold text-danger mb-2"><?php echo e(__('Danger Zone')); ?></h2>
            <p class="text-sm text-muted mb-4"><?php echo e(__('These actions are permanent and cannot be undone.')); ?></p>

            <div class="space-y-3">
                <div class="flex items-center justify-between p-4 bg-danger/10 rounded-xl border border-danger/20">
                    <div>
                        <p class="text-sm font-medium text-red-900"><?php echo e(__('Deactivate Account')); ?></p>
                        <p class="text-xs text-danger mt-0.5"><?php echo e(__('Temporarily disable your account. You can reactivate later.')); ?></p>
                    </div>
                    <form method="POST" action="<?php echo e(url('/settings/deactivate-account')); ?>"
                          @submit.prevent="if(confirm('Are you sure you want to deactivate your account? You can reactivate by contacting support.')) $el.submit()">
                        <?php echo csrf_field(); ?>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-danger border border-red-300 rounded-xl hover:bg-danger/15 transition-colors disabled:opacity-50">
                            <?php echo e(__('Deactivate')); ?>

                        </button>
                    </form>
                </div>
                <div class="p-4 bg-danger/10 rounded-xl border border-danger/20" x-data="{ showDeleteForm: false }">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-red-900"><?php echo e(__('Delete Account')); ?></p>
                            <p class="text-xs text-danger mt-0.5"><?php echo e(__('Permanently delete your account and all associated data.')); ?></p>
                        </div>
                        <button @click="showDeleteForm = !showDeleteForm"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-xl hover:bg-red-700 transition-colors">
                            <?php echo e(__('Delete Account')); ?>

                        </button>
                    </div>
                    <form x-show="showDeleteForm" x-transition method="POST" action="<?php echo e(url('/settings/delete-account')); ?>" class="mt-4 pt-4 border-t border-danger/20 space-y-3" style="display: none;">
                        <?php echo csrf_field(); ?>
                        <div>
                            <label class="block text-xs font-medium text-red-900 mb-1"><?php echo e(__('Confirm your password')); ?></label>
                            <input type="password" name="password" required class="w-full px-3 py-2 text-sm border border-danger/30 rounded-xl focus:outline-none focus:ring-2 focus:ring-danger/40 bg-surface" placeholder="<?php echo e(__('Enter your current password')); ?>">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-danger mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-red-900 mb-1"><?php echo e(__('Type')); ?> <strong>DELETE</strong> <?php echo e(__('to confirm')); ?></label>
                            <input type="text" name="confirmation" required pattern="DELETE" class="w-full px-3 py-2 text-sm border border-danger/30 rounded-xl focus:outline-none focus:ring-2 focus:ring-danger/40 bg-surface" placeholder="<?php echo e(__('Type DELETE')); ?>">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['confirmation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-danger mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-xl hover:bg-red-700 transition-colors">
                            <?php echo e(__('Permanently Delete My Account')); ?>

                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala9d35bca55314290701d0fd9db1a187a)): ?>
<?php $attributes = $__attributesOriginala9d35bca55314290701d0fd9db1a187a; ?>
<?php unset($__attributesOriginala9d35bca55314290701d0fd9db1a187a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala9d35bca55314290701d0fd9db1a187a)): ?>
<?php $component = $__componentOriginala9d35bca55314290701d0fd9db1a187a; ?>
<?php unset($__componentOriginala9d35bca55314290701d0fd9db1a187a); ?>
<?php endif; ?>
<?php /**PATH /home/dahimail.com/public_html/resources/views/settings/account.blade.php ENDPATH**/ ?>