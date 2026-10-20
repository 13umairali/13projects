<?php if (isset($component)) { $__componentOriginalc8c9fd5d7827a77a31381de67195f0c3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc8c9fd5d7827a77a31381de67195f0c3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.admin','data' => ['title' => __('Notification Settings'),'subtitle' => __('Configure email and Slack notifications for admin events.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.admin'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Notification Settings')),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Configure email and Slack notifications for admin events.'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <div class="space-y-6">

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="p-4 bg-success/10 border border-success/20 rounded-xl text-sm font-medium text-success flex items-center gap-2">
            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <?php echo e(session('success')); ?>

        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php
            $getSetting = function($key, $default = '') {
                static $cache = null;
                if ($cache === null) {
                    try {
                        $cache = \Illuminate\Support\Facades\DB::table('system_settings')->pluck('value', 'key')->toArray();
                    } catch (\Exception $e) {
                        $cache = [];
                    }
                }
                return $cache[$key] ?? $default;
            };
        ?>

        
        <form method="POST" action="<?php echo e(route('admin.settings.update')); ?>">
            <?php echo csrf_field(); ?>
            <div class="panel overflow-hidden">
                <div class="px-6 py-4 border-b border-border/50 bg-surface/50">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-brand/15 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-ink"><?php echo e(__('Email Notifications')); ?></h2>
                            <p class="text-sm text-muted"><?php echo e(__('Choose which events trigger email alerts to admin')); ?></p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <?php
                        $emailEvents = [
                            ['key' => 'notify_new_user', 'label' => __('New User Registration'), 'desc' => __('When a new user signs up for the platform'), 'default' => 'true'],
                            ['key' => 'notify_payment_success', 'label' => __('Successful Payment'), 'desc' => __('When a subscription payment is processed successfully'), 'default' => 'true'],
                            ['key' => 'notify_payment_failed', 'label' => __('Failed Payment'), 'desc' => __('When a subscription payment fails or is declined'), 'default' => 'true'],
                            ['key' => 'notify_new_ticket', 'label' => __('New Support Ticket'), 'desc' => __('When a user creates a new support ticket'), 'default' => 'true'],
                            ['key' => 'notify_system_alert', 'label' => __('System Alert'), 'desc' => __('Critical system errors, high resource usage, or downtime'), 'default' => 'true'],
                            ['key' => 'notify_plan_change', 'label' => __('Plan Upgrade/Downgrade'), 'desc' => __('When a user changes their subscription plan'), 'default' => 'false'],
                            ['key' => 'notify_user_suspension', 'label' => __('User Suspension'), 'desc' => __('When a user account is suspended or banned'), 'default' => 'true'],
                            ['key' => 'notify_ai_budget', 'label' => __('AI Budget Alert'), 'desc' => __('When AI spending reaches the configured threshold'), 'default' => 'true'],
                            ['key' => 'notify_high_bounce', 'label' => __('High Bounce Rate'), 'desc' => __('When email bounce rate exceeds 5%'), 'default' => 'false'],
                            ['key' => 'notify_daily_summary', 'label' => __('Daily Summary Report'), 'desc' => __('Receive a daily digest of platform activity'), 'default' => 'false'],
                        ];
                        ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $emailEvents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                        <?php $isEnabled = $getSetting($event['key'], $event['default']) === 'true'; ?>
                        <div class="flex items-center justify-between p-4 bg-surface rounded-xl hover:bg-surface transition-colors">
                            <div>
                                <h4 class="text-sm font-semibold text-ink"><?php echo e($event['label']); ?></h4>
                                <p class="text-xs text-muted mt-0.5"><?php echo e($event['desc']); ?></p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="<?php echo e($event['key']); ?>" value="false">
                                <input type="checkbox" name="<?php echo e($event['key']); ?>" value="true" class="sr-only peer" <?php echo e($isEnabled ? 'checked' : ''); ?>>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-brand/40/20 rounded-full peer peer-checked:after:translate-x-5 after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-surface-2 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-success/100"></div>
                            </label>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>

                    <div class="flex justify-end mt-5">
                        <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-brand rounded-xl hover:bg-brand-strong shadow-lg shadow-soft transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <?php echo e(__('Save Email Preferences')); ?>

                        </button>
                    </div>
                </div>
            </div>
        </form>

        
        <form method="POST" action="<?php echo e(route('admin.settings.update')); ?>">
            <?php echo csrf_field(); ?>
            <div class="panel overflow-hidden">
                <div class="px-6 py-4 border-b border-border/50 bg-surface/50">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-brand/15 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-ink"><?php echo e(__('Slack Notifications')); ?></h2>
                            <p class="text-sm text-muted"><?php echo e(__('Send alerts to your Slack workspace')); ?></p>
                        </div>
                    </div>
                </div>
                <div class="p-6 space-y-5">
                    <div>
                        <label class="block text-xs font-bold text-ink/80 uppercase tracking-wider mb-1.5"><?php echo e(__('Slack Webhook URL')); ?></label>
                        <input type="url" name="slack_webhook_url" value="<?php echo e($getSetting('slack_webhook_url', '')); ?>" placeholder="https://hooks.slack.com/services/..." class="w-full px-4 py-2.5 text-sm border border-border rounded-xl focus:ring-2 focus:ring-brand/40/20 focus:border-indigo-500 transition-all font-mono text-xs">
                    </div>

                    <div class="space-y-3">
                        <?php
                        $slackEvents = [
                            ['key' => 'slack_notify_new_user', 'label' => __('New User Registration'), 'default' => 'true'],
                            ['key' => 'slack_notify_payment_failed', 'label' => __('Failed Payment'), 'default' => 'true'],
                            ['key' => 'slack_notify_new_ticket', 'label' => __('New Support Ticket'), 'default' => 'true'],
                            ['key' => 'slack_notify_system_alert', 'label' => __('System Alert'), 'default' => 'true'],
                            ['key' => 'slack_notify_ai_budget', 'label' => __('AI Budget Alert'), 'default' => 'false'],
                        ];
                        ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $slackEvents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $se): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                        <?php $seEnabled = $getSetting($se['key'], $se['default']) === 'true'; ?>
                        <div class="flex items-center justify-between p-3 bg-surface rounded-xl">
                            <span class="text-sm font-medium text-ink/80"><?php echo e($se['label']); ?></span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="<?php echo e($se['key']); ?>" value="false">
                                <input type="checkbox" name="<?php echo e($se['key']); ?>" value="true" class="sr-only peer" <?php echo e($seEnabled ? 'checked' : ''); ?>>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-brand/40/20 rounded-full peer peer-checked:after:translate-x-5 after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-surface-2 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-success/100"></div>
                            </label>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-brand rounded-xl hover:bg-brand-strong shadow-lg shadow-soft transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <?php echo e(__('Save Slack Settings')); ?>

                        </button>
                    </div>
                </div>
            </div>
        </form>

        
        <form method="POST" action="<?php echo e(route('admin.settings.update')); ?>">
            <?php echo csrf_field(); ?>
            <div class="panel overflow-hidden">
                <div class="px-6 py-4 border-b border-border/50 bg-surface/50">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-teal-100 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-ink"><?php echo e(__('Admin Email Recipients')); ?></h2>
                            <p class="text-sm text-muted"><?php echo e(__('Comma-separated list of emails that receive admin notifications')); ?></p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div>
                        <label class="block text-xs font-bold text-ink/80 uppercase tracking-wider mb-1.5"><?php echo e(__('Notification Recipients')); ?></label>
                        <textarea rows="3" name="admin_notification_emails" class="w-full px-4 py-2.5 text-sm border border-border rounded-xl focus:ring-2 focus:ring-brand/40/20 focus:border-indigo-500 transition-all resize-none font-mono" placeholder="admin@example.com, dev@example.com"><?php echo e($getSetting('admin_notification_emails', '')); ?></textarea>
                        <p class="text-[10px] text-muted mt-1"><?php echo e(__('One email per line or comma-separated. These addresses receive all enabled admin notifications.')); ?></p>
                    </div>
                    <div class="flex justify-end mt-4">
                        <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-brand rounded-xl hover:bg-brand-strong shadow-lg shadow-soft transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <?php echo e(__('Save Recipients')); ?>

                        </button>
                    </div>
                </div>
            </div>
        </form>

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
<?php /**PATH /home/dahimail.com/public_html/resources/views/admin/notifications.blade.php ENDPATH**/ ?>