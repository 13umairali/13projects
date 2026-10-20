<?php if (isset($component)) { $__componentOriginalc8c9fd5d7827a77a31381de67195f0c3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc8c9fd5d7827a77a31381de67195f0c3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.admin','data' => ['title' => __('Audit Log'),'subtitle' => __('Track all admin actions and system events for security and compliance.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.admin'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Audit Log')),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Track all admin actions and system events for security and compliance.'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <div class="space-y-6">

        
        <div class="panel p-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="panel-heading"><?php echo e(__('Audit Log')); ?></p>
                    <p class="mt-2 text-sm text-muted"><?php echo e(__('Track admin actions, system events, and security changes.')); ?></p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <a href="<?php echo e(route('admin.audit-log.export', request()->query())); ?>" class="btn-secondary"><?php echo e(__('Export CSV')); ?></a>
                </div>
            </div>

            <form method="get" action="<?php echo e(route('admin.audit-log')); ?>" class="filter-form mt-6 grid gap-4 lg:grid-cols-[1.4fr_1fr_1fr_1fr_1fr_auto] md:grid-cols-[1.4fr_1fr_1fr] sm:grid-cols-2">
                <div>
                    <label class="sr-only" for="search"><?php echo e(__('Search')); ?></label>
                    <input id="search" name="search" value="<?php echo e(request('search')); ?>" class="input-field" placeholder="<?php echo e(__('Search by actor or event')); ?>">
                </div>
                <div>
                    <label class="sr-only" for="event"><?php echo e(__('Event')); ?></label>
                    <select id="event" name="event" class="input-field">
                        <option value=""><?php echo e(__('All events')); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $eventTypes ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $eventType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                            <option value="<?php echo e($eventType); ?>" <?php if(request('event') === $eventType): echo 'selected'; endif; ?>><?php echo e(str_replace('_', ' ', ucfirst($eventType))); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </div>
                <div>
                    <label class="sr-only" for="actor_type"><?php echo e(__('Actor type')); ?></label>
                    <select id="actor_type" name="actor_type" class="input-field">
                        <option value=""><?php echo e(__('All actors')); ?></option>
                        <option value="admin" <?php if(request('actor_type') === 'admin'): echo 'selected'; endif; ?>><?php echo e(__('Admin')); ?></option>
                        <option value="user" <?php if(request('actor_type') === 'user'): echo 'selected'; endif; ?>><?php echo e(__('User')); ?></option>
                    </select>
                </div>
                <div>
                    <label class="sr-only" for="from"><?php echo e(__('From date')); ?></label>
                    <input id="from" type="date" name="from" value="<?php echo e(request('from')); ?>" class="input-field">
                </div>
                <div>
                    <label class="sr-only" for="to"><?php echo e(__('To date')); ?></label>
                    <input id="to" type="date" name="to" value="<?php echo e(request('to')); ?>" class="input-field">
                </div>
                <div class="flex items-center gap-3">
                    <button class="btn-secondary" type="submit"><?php echo e(__('Filter')); ?></button>
                    <a href="<?php echo e(route('admin.audit-log')); ?>" class="btn-secondary"><?php echo e(__('Reset')); ?></a>
                </div>
            </form>
        </div>

        
        <div class="panel p-6" x-data="{
            view: localStorage.getItem('admin-audit-view') || 'list'
        }" x-init="$watch('view', v => localStorage.setItem('admin-audit-view', v))">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="panel-heading"><?php echo e(__('Log Directory')); ?></p>
                    <p class="mt-2 text-sm text-muted"><?php echo e(__('Showing')); ?> <?php echo e($logs->firstItem() ?? 0); ?>-<?php echo e($logs->lastItem() ?? 0); ?> <?php echo e(__('of')); ?> <?php echo e($logs->total()); ?> <?php echo e(__('log entries.')); ?></p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" @click="view = 'list'" :class="view === 'list' ? 'btn-primary' : 'btn-secondary'"><?php echo e(__('List')); ?></button>
                    <button type="button" @click="view = 'grid'" :class="view === 'grid' ? 'btn-primary' : 'btn-secondary'"><?php echo e(__('Grid')); ?></button>
                    <button type="button" class="btn-secondary" onclick="window.print()"><?php echo e(__('Print')); ?></button>
                    <a href="<?php echo e(route('admin.audit-log.export', request()->query())); ?>" class="btn-secondary"><?php echo e(__('Export')); ?></a>
                </div>
            </div>

            
            <div x-show="view === 'list'" class="mt-6 overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs uppercase tracking-[0.2em] text-muted">
                        <tr>
                            <th class="pb-3"><?php echo e(__('Date & Time')); ?></th>
                            <th class="pb-3"><?php echo e(__('Actor')); ?></th>
                            <th class="pb-3"><?php echo e(__('Event')); ?></th>
                            <th class="pb-3"><?php echo e(__('Target')); ?></th>
                            <th class="pb-3"><?php echo e(__('IP Address')); ?></th>
                            <th class="pb-3"><?php echo e(__('Details')); ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border/60">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                        <tr>
                            <td class="py-4">
                                <span class="text-sm text-ink font-mono"><?php echo e(\Carbon\Carbon::parse($log->created_at)->format('M j, Y H:i:s')); ?></span>
                            </td>
                            <td class="py-4">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand/10 text-brand text-xs font-bold">
                                        <?php echo e(strtoupper(substr($log->actor_name ?? 'SY', 0, 2))); ?>

                                    </span>
                                    <div>
                                        <span class="font-semibold text-ink"><?php echo e($log->actor_name ?? __('System')); ?></span>
                                        <p class="text-xs text-muted"><?php echo e(ucfirst($log->actor_type ?? 'system')); ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4">
                                <span class="text-sm font-semibold text-ink"><?php echo e(str_replace('_', ' ', ucfirst($log->event))); ?></span>
                            </td>
                            <td class="py-4 text-sm text-ink">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($log->auditable_type): ?>
                                    <?php echo e(class_basename($log->auditable_type)); ?> #<?php echo e($log->auditable_id); ?>

                                <?php else: ?>
                                    --
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="py-4">
                                <code class="text-xs font-mono text-muted"><?php echo e($log->ip_address ?? '--'); ?></code>
                            </td>
                            <td class="py-4 text-sm text-ink max-w-[250px] truncate">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($log->new_values): ?>
                                    <?php $vals = json_decode($log->new_values, true); ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(is_array($vals)): ?>
                                        <?php echo e(\Illuminate\Support\Str::limit(implode(', ', array_map(fn($k, $v) => "$k: $v", array_keys($vals), array_values($vals))), 80)); ?>

                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php elseif($log->old_values): ?>
                                    <?php $vals = json_decode($log->old_values, true); ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(is_array($vals)): ?>
                                        <?php echo e(\Illuminate\Support\Str::limit(implode(', ', array_map(fn($k, $v) => "$k: $v", array_keys($vals), array_values($vals))), 80)); ?>

                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php else: ?>
                                    --
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                        </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr>
                            <td colspan="6" class="py-6 text-center text-sm text-muted"><?php echo e(__('No audit logs found.')); ?></td>
                        </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>

            
            <div x-show="view === 'grid'" x-cloak class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                <div class="panel p-4 space-y-2">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand/10 text-brand text-xs font-bold">
                            <?php echo e(strtoupper(substr($log->actor_name ?? 'SY', 0, 2))); ?>

                        </span>
                        <div class="min-w-0">
                            <span class="font-semibold text-ink text-sm"><?php echo e($log->actor_name ?? __('System')); ?></span>
                            <p class="text-xs text-muted"><?php echo e(ucfirst($log->actor_type ?? 'system')); ?></p>
                        </div>
                    </div>
                    <div class="text-sm font-semibold text-ink"><?php echo e(str_replace('_', ' ', ucfirst($log->event))); ?></div>
                    <div class="flex items-center justify-between text-xs text-muted">
                        <span>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($log->auditable_type): ?>
                                <?php echo e(class_basename($log->auditable_type)); ?> #<?php echo e($log->auditable_id); ?>

                            <?php else: ?>
                                --
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </span>
                        <code class="font-mono"><?php echo e($log->ip_address ?? '--'); ?></code>
                    </div>
                    <div class="text-xs text-muted font-mono"><?php echo e(\Carbon\Carbon::parse($log->created_at)->format('M j, Y H:i:s')); ?></div>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <div class="col-span-full py-6 text-center text-sm text-muted"><?php echo e(__('No audit logs found.')); ?></div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($logs->hasPages()): ?>
            <div class="mt-4 border-t border-border/60 pt-4">
                <?php echo e($logs->withQueryString()->links()); ?>

            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
<?php /**PATH /home/dahimail.com/public_html/resources/views/admin/audit-log/index.blade.php ENDPATH**/ ?>