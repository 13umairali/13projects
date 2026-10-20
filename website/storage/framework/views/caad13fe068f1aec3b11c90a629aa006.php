<?php if (isset($component)) { $__componentOriginalc8c9fd5d7827a77a31381de67195f0c3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc8c9fd5d7827a77a31381de67195f0c3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.admin','data' => ['title' => __('Email Deliverability'),'subtitle' => __('Monitor email delivery rates, bounces, and failures.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.admin'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Email Deliverability')),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Monitor email delivery rates, bounces, and failures.'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <div class="space-y-6">

        <?php
            $total = (int) ($stats->total ?? 0);
            $delivered = (int) ($stats->delivered ?? 0);
            $bounced = (int) ($stats->bounced ?? 0);
            $failed = (int) ($stats->failed ?? 0);
            $deliveryRate = $total > 0 ? round(($delivered / $total) * 100, 1) : 0;
            $bounceRate = $total > 0 ? round(($bounced / $total) * 100, 1) : 0;
            $failRate = $total > 0 ? round(($failed / $total) * 100, 1) : 0;
        ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($total === 0): ?>
            
            <div class="panel p-12 text-center">
                <div class="w-16 h-16 bg-cyan-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-ink mb-1"><?php echo e(__('No email data yet')); ?></h3>
                <p class="text-sm text-muted max-w-md mx-auto"><?php echo e(__('Outbound email delivery metrics will appear here once emails are sent through the platform.')); ?></p>
            </div>
        <?php else: ?>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <?php
                $deliveryCards = [
                    ['label' => __('Total Emails Sent'), 'value' => number_format($total), 'icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'color' => 'from-indigo-500 to-blue-600', 'shadow' => 'shadow-soft'],
                    ['label' => __('Delivery Rate'), 'value' => $deliveryRate . '%', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'from-green-500 to-emerald-600', 'shadow' => 'shadow-green-200'],
                    ['label' => __('Bounce Rate'), 'value' => $bounceRate . '%', 'icon' => 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'from-amber-500 to-orange-600', 'shadow' => 'shadow-amber-200'],
                    ['label' => __('Failure Rate'), 'value' => $failRate . '%', 'icon' => 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636', 'color' => 'from-red-500 to-rose-600', 'shadow' => 'shadow-red-200'],
                ];
                ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $deliveryCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                <div class="panel p-5 hover:shadow-md transition-all duration-200">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 bg-gradient-to-br <?php echo e($card['color']); ?> rounded-xl flex items-center justify-center shadow-lg <?php echo e($card['shadow']); ?>">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo e($card['icon']); ?>"/></svg>
                        </div>
                        <span class="text-xs font-medium text-muted"><?php echo e($card['label']); ?></span>
                    </div>
                    <p class="text-2xl font-extrabold text-ink"><?php echo e($card['value']); ?></p>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>

            
            <div class="panel p-6">
                <h2 class="text-lg font-bold text-ink mb-1"><?php echo e(__('Delivery Breakdown')); ?></h2>
                <p class="text-xs text-muted mb-4"><?php echo e(__('All outbound messages by status')); ?></p>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-success/10 border border-success/20 rounded-xl p-4 text-center">
                        <p class="text-2xl font-extrabold text-success"><?php echo e(number_format($delivered)); ?></p>
                        <p class="text-xs font-semibold text-success mt-1"><?php echo e(__('Delivered / Sent')); ?></p>
                    </div>
                    <div class="bg-warning/10 border border-warning/20 rounded-xl p-4 text-center">
                        <p class="text-2xl font-extrabold text-warning"><?php echo e(number_format($bounced)); ?></p>
                        <p class="text-xs font-semibold text-amber-600 mt-1"><?php echo e(__('Bounced')); ?></p>
                    </div>
                    <div class="bg-danger/10 border border-danger/20 rounded-xl p-4 text-center">
                        <p class="text-2xl font-extrabold text-danger"><?php echo e(number_format($failed)); ?></p>
                        <p class="text-xs font-semibold text-danger mt-1"><?php echo e(__('Failed')); ?></p>
                    </div>
                </div>
            </div>

            
            <div class="panel overflow-hidden">
                <div class="px-6 py-4 border-b border-border/50 bg-surface/50">
                    <h2 class="text-lg font-bold text-ink"><?php echo e(__('Recent Bounced Messages')); ?></h2>
                    <p class="text-xs text-muted mt-0.5"><?php echo e(__('Last')); ?> <?php echo e($recentBounces->count()); ?> <?php echo e(__('bounced emails')); ?></p>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($recentBounces->count() > 0): ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-surface border-b border-border/50">
                            <tr>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold text-muted uppercase tracking-wider"><?php echo e(__('ID')); ?></th>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold text-muted uppercase tracking-wider"><?php echo e(__('Subject')); ?></th>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold text-muted uppercase tracking-wider"><?php echo e(__('Error')); ?></th>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold text-muted uppercase tracking-wider"><?php echo e(__('Date')); ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/60">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $recentBounces; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $msg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                            <tr class="hover:bg-surface transition-colors">
                                <td class="px-4 py-2.5 text-xs font-mono text-ink/80">#<?php echo e($msg->id); ?></td>
                                <td class="px-4 py-2.5 text-xs text-ink/80 max-w-xs truncate"><?php echo e($msg->subject ?? __('(no subject)')); ?></td>
                                <td class="px-4 py-2.5 text-xs text-danger max-w-xs truncate"><?php echo e($msg->delivery_error ?? __('Unknown error')); ?></td>
                                <td class="px-4 py-2.5 text-xs text-muted"><?php echo e($msg->created_at?->format('M j, Y H:i') ?? '--'); ?></td>
                            </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="p-8 text-center">
                    <p class="text-sm text-muted"><?php echo e(__('No bounced messages found.')); ?></p>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

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
<?php /**PATH /home/dahimail.com/public_html/resources/views/admin/email-deliverability.blade.php ENDPATH**/ ?>