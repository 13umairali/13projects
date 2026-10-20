<?php if (isset($component)) { $__componentOriginalc8c9fd5d7827a77a31381de67195f0c3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc8c9fd5d7827a77a31381de67195f0c3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.admin','data' => ['title' => __('Blocked IPs'),'subtitle' => __('Manage IP addresses blocked from accessing the application.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.admin'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Blocked IPs')),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Manage IP addresses blocked from accessing the application.'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <div class="space-y-6">

        
        <div class="panel p-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="panel-heading"><?php echo e(__('Blocked IPs')); ?></p>
                    <p class="mt-2 text-sm text-muted"><?php echo e(__('Manage blocked IP addresses and access restrictions.')); ?></p>
                </div>
            </div>

            
            <div class="mt-6 border-t border-border/60 pt-6">
                <p class="text-sm font-semibold text-ink mb-4"><?php echo e(__('Block New IP Address')); ?></p>
                <form method="POST" action="<?php echo e(route('admin.blocked-ips.store')); ?>" class="filter-form grid gap-4 lg:grid-cols-[1.4fr_1fr_1fr_auto] md:grid-cols-[1.4fr_1fr_1fr] sm:grid-cols-2">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label class="sr-only" for="ip_address"><?php echo e(__('IP Address')); ?></label>
                        <input id="ip_address" type="text" name="ip_address" value="<?php echo e(old('ip_address')); ?>" class="input-field" placeholder="<?php echo e(__('e.g. 192.168.1.100')); ?>" required>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['ip_address'];
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
                    <div>
                        <label class="sr-only" for="reason"><?php echo e(__('Reason')); ?></label>
                        <input id="reason" type="text" name="reason" value="<?php echo e(old('reason')); ?>" class="input-field" placeholder="<?php echo e(__('e.g. Brute force attack, spam')); ?>">
                    </div>
                    <div>
                        <label class="sr-only" for="duration"><?php echo e(__('Duration')); ?></label>
                        <select id="duration" name="duration" class="input-field">
                            <option value="permanent"><?php echo e(__('Permanent')); ?></option>
                            <option value="1h"><?php echo e(__('1 Hour')); ?></option>
                            <option value="24h"><?php echo e(__('24 Hours')); ?></option>
                            <option value="7d"><?php echo e(__('7 Days')); ?></option>
                            <option value="30d"><?php echo e(__('30 Days')); ?></option>
                        </select>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="submit" class="btn-danger"><?php echo e(__('Block IP')); ?></button>
                    </div>
                </form>
            </div>

            
            <form method="get" action="<?php echo e(route('admin.blocked-ips.index')); ?>" class="filter-form mt-6 border-t border-border/60 pt-6 grid gap-4 lg:grid-cols-[1.4fr_1fr_auto] sm:grid-cols-2">
                <div>
                    <label class="sr-only" for="search"><?php echo e(__('Search')); ?></label>
                    <input id="search" name="search" value="<?php echo e(request('search')); ?>" class="input-field" placeholder="<?php echo e(__('Search by IP, reason, or blocked by')); ?>">
                </div>
                <div>
                    <label class="sr-only" for="filter_status"><?php echo e(__('Status')); ?></label>
                    <select id="filter_status" name="status" class="input-field">
                        <option value=""><?php echo e(__('All statuses')); ?></option>
                        <option value="permanent" <?php if(request('status') === 'permanent'): echo 'selected'; endif; ?>><?php echo e(__('Permanent')); ?></option>
                        <option value="active" <?php if(request('status') === 'active'): echo 'selected'; endif; ?>><?php echo e(__('Active (Temporary)')); ?></option>
                        <option value="expired" <?php if(request('status') === 'expired'): echo 'selected'; endif; ?>><?php echo e(__('Expired')); ?></option>
                    </select>
                </div>
                <div class="flex items-center gap-3">
                    <button class="btn-secondary" type="submit"><?php echo e(__('Filter')); ?></button>
                    <a href="<?php echo e(route('admin.blocked-ips.index')); ?>" class="btn-secondary"><?php echo e(__('Reset')); ?></a>
                </div>
            </form>
        </div>

        
        <div class="panel p-6" x-data="{
            selected: [],
            selectAll: false,
            view: localStorage.getItem('admin-blocked-ips-view') || 'list',
            allIds: [<?php echo e($blockedIps->pluck('id')->join(',')); ?>]
        }" x-init="$watch('view', v => localStorage.setItem('admin-blocked-ips-view', v)); $watch('selectAll', v => { selected = v ? [...allIds] : [] })">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="panel-heading"><?php echo e(__('Blocked IP Directory')); ?></p>
                    <p class="mt-2 text-sm text-muted"><?php echo e(__('Showing')); ?> <?php echo e($blockedIps->count()); ?> <?php echo e(__('of')); ?> <?php echo e($blockedIps->total()); ?> <?php echo e(__('blocked IPs.')); ?></p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" @click="view = 'list'" :class="view === 'list' ? 'btn-primary' : 'btn-secondary'"><?php echo e(__('List')); ?></button>
                    <button type="button" @click="view = 'grid'" :class="view === 'grid' ? 'btn-primary' : 'btn-secondary'"><?php echo e(__('Grid')); ?></button>
                    <button type="button" class="btn-secondary" onclick="window.print()"><?php echo e(__('Print')); ?></button>
                    <button x-show="selected.length > 0" x-cloak type="button" class="btn-danger" @click="$dispatch('open-modal', 'bulk-unblock-ips')" x-text="'Bulk Unblock (' + selected.length + ')'"></button>
                </div>
            </div>

            
            <div x-show="view === 'list'" class="mt-6 overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs uppercase tracking-[0.2em] text-muted">
                        <tr>
                            <th class="pb-3">
                                <input type="checkbox" x-model="selectAll" class="h-4 w-4 rounded border-border bg-surface-2/80 text-brand focus:ring-brand/40">
                            </th>
                            <th class="pb-3"><?php echo e(__('IP Address')); ?></th>
                            <th class="pb-3"><?php echo e(__('Reason')); ?></th>
                            <th class="pb-3"><?php echo e(__('Blocked By')); ?></th>
                            <th class="pb-3"><?php echo e(__('Status')); ?></th>
                            <th class="pb-3"><?php echo e(__('Blocked Until')); ?></th>
                            <th class="pb-3"><?php echo e(__('Created')); ?></th>
                            <th class="pb-3 text-right"><?php echo e(__('Actions')); ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border/60">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $blockedIps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $blocked): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                            <?php
                                $isPermanent = is_null($blocked->blocked_until);
                                $isExpired   = !$isPermanent && \Carbon\Carbon::parse($blocked->blocked_until)->isPast();
                            ?>
                        <tr>
                            <td class="py-4">
                                <input type="checkbox" value="<?php echo e($blocked->id); ?>" x-model.number="selected" class="h-4 w-4 rounded border-border bg-surface-2/80 text-brand focus:ring-brand/40">
                            </td>
                            <td class="py-4">
                                <span class="font-mono text-sm font-semibold text-ink"><?php echo e($blocked->ip_address); ?></span>
                            </td>
                            <td class="py-4 text-sm text-ink"><?php echo e($blocked->reason ?? '-'); ?></td>
                            <td class="py-4 text-sm text-ink"><?php echo e($blocked->blocked_by ?? __('System')); ?></td>
                            <td class="py-4">
                                <span class="text-sm font-semibold text-ink">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isPermanent): ?>
                                        <?php echo e(__('Permanent')); ?>

                                    <?php elseif($isExpired): ?>
                                        <?php echo e(__('Expired')); ?>

                                    <?php else: ?>
                                        <?php echo e(__('Active')); ?>

                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </span>
                            </td>
                            <td class="py-4 text-sm text-ink">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isPermanent): ?>
                                    <?php echo e(__('Never')); ?>

                                <?php else: ?>
                                    <?php echo e(\Carbon\Carbon::parse($blocked->blocked_until)->format('M j, Y H:i')); ?>

                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="py-4 text-sm text-ink"><?php echo e(\Carbon\Carbon::parse($blocked->created_at)->format('M j, Y')); ?></td>
                            <td class="py-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <button type="button" class="btn-secondary" x-data @click="$dispatch('open-modal', 'confirm-unblock-<?php echo e($blocked->id); ?>')"><?php echo e(__('Unblock')); ?></button>
                                </div>
                            </td>
                        </tr>

                        
                        <?php if (isset($component)) { $__componentOriginal374a8b4f0d20c1f5f1a223240a48bd27 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal374a8b4f0d20c1f5f1a223240a48bd27 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-modal','data' => ['name' => 'confirm-unblock-'.e($blocked->id).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'confirm-unblock-'.e($blocked->id).'']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                            <form method="POST" action="<?php echo e(route('admin.blocked-ips.destroy', $blocked->id)); ?>" class="p-6 space-y-4">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <div>
                                    <p class="text-lg font-semibold text-ink"><?php echo e(__('Unblock')); ?> "<?php echo e($blocked->ip_address); ?>"?</p>
                                    <p class="mt-2 text-sm text-muted"><?php echo e(__('This IP address will be able to access the application again. You can always re-block it later.')); ?></p>
                                </div>
                                <div class="flex justify-end gap-3">
                                    <button type="button" class="btn-secondary" x-on:click="$dispatch('close')"><?php echo e(__('Cancel')); ?></button>
                                    <button type="submit" class="btn-danger"><?php echo e(__('Unblock IP')); ?></button>
                                </div>
                            </form>
                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal374a8b4f0d20c1f5f1a223240a48bd27)): ?>
<?php $attributes = $__attributesOriginal374a8b4f0d20c1f5f1a223240a48bd27; ?>
<?php unset($__attributesOriginal374a8b4f0d20c1f5f1a223240a48bd27); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal374a8b4f0d20c1f5f1a223240a48bd27)): ?>
<?php $component = $__componentOriginal374a8b4f0d20c1f5f1a223240a48bd27; ?>
<?php unset($__componentOriginal374a8b4f0d20c1f5f1a223240a48bd27); ?>
<?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr>
                            <td colspan="8" class="py-6 text-center text-sm text-muted"><?php echo e(__('No blocked IPs found.')); ?></td>
                        </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>

            
            <div x-show="view === 'grid'" x-cloak class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $blockedIps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $blocked): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                    <?php
                        $isPermanent = is_null($blocked->blocked_until);
                        $isExpired   = !$isPermanent && \Carbon\Carbon::parse($blocked->blocked_until)->isPast();
                    ?>
                <div class="panel p-4 space-y-2">
                    <div class="flex items-center gap-3">
                        <input type="checkbox" value="<?php echo e($blocked->id); ?>" x-model.number="selected" class="h-4 w-4 rounded border-border bg-surface-2/80 text-brand focus:ring-brand/40">
                        <span class="font-mono text-sm font-semibold text-ink"><?php echo e($blocked->ip_address); ?></span>
                    </div>
                    <div class="text-sm text-ink"><?php echo e($blocked->reason ?? __('No reason')); ?></div>
                    <div class="flex items-center justify-between text-xs text-muted">
                        <span><?php echo e($blocked->blocked_by ?? __('System')); ?></span>
                        <span class="font-semibold text-ink">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isPermanent): ?> <?php echo e(__('Permanent')); ?> <?php elseif($isExpired): ?> <?php echo e(__('Expired')); ?> <?php else: ?> <?php echo e(__('Active')); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </span>
                    </div>
                    <div class="pt-2 border-t border-border/60">
                        <button type="button" class="btn-secondary text-xs" x-data @click="$dispatch('open-modal', 'confirm-unblock-<?php echo e($blocked->id); ?>')"><?php echo e(__('Unblock')); ?></button>
                    </div>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <div class="col-span-full py-6 text-center text-sm text-muted"><?php echo e(__('No blocked IPs found.')); ?></div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($blockedIps->hasPages()): ?>
            <div class="mt-4 border-t border-border/60 pt-4">
                <?php echo e($blockedIps->withQueryString()->links()); ?>

            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    
    <?php if (isset($component)) { $__componentOriginal374a8b4f0d20c1f5f1a223240a48bd27 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal374a8b4f0d20c1f5f1a223240a48bd27 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin-modal','data' => ['name' => 'bulk-unblock-ips']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'bulk-unblock-ips']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <form method="POST" action="<?php echo e(route('admin.blocked-ips.bulk-destroy')); ?>" class="p-6 space-y-4"
              x-data @submit="
                  const checkboxes = document.querySelectorAll('input[type=checkbox][x-model\\.number=selected]:checked');
                  checkboxes.forEach(cb => {
                      const input = document.createElement('input');
                      input.type = 'hidden'; input.name = 'ids[]'; input.value = cb.value;
                      $el.appendChild(input);
                  });
              ">
            <?php echo csrf_field(); ?>
            <div>
                <p class="text-lg font-semibold text-ink"><?php echo e(__('Unblock selected IPs?')); ?></p>
                <p class="mt-2 text-sm text-muted"><?php echo __('The selected IP address(es) will be <strong class="text-danger">removed from the block list</strong> and will be able to access the application again.'); ?></p>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" class="btn-secondary" x-on:click="$dispatch('close')"><?php echo e(__('Cancel')); ?></button>
                <button type="submit" class="btn-danger"><?php echo e(__('Unblock All Selected')); ?></button>
            </div>
        </form>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal374a8b4f0d20c1f5f1a223240a48bd27)): ?>
<?php $attributes = $__attributesOriginal374a8b4f0d20c1f5f1a223240a48bd27; ?>
<?php unset($__attributesOriginal374a8b4f0d20c1f5f1a223240a48bd27); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal374a8b4f0d20c1f5f1a223240a48bd27)): ?>
<?php $component = $__componentOriginal374a8b4f0d20c1f5f1a223240a48bd27; ?>
<?php unset($__componentOriginal374a8b4f0d20c1f5f1a223240a48bd27); ?>
<?php endif; ?>
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
<?php /**PATH /home/dahimail.com/public_html/resources/views/admin/blocked-ips/index.blade.php ENDPATH**/ ?>