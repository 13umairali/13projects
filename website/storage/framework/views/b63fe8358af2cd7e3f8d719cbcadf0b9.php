<div class="space-y-4">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($insights) > 0): ?>
        
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 bg-brand/10 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center">
                <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'sparkles','class' => 'w-3.5 h-3.5 text-brand dark:text-indigo-400']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'sparkles','class' => 'w-3.5 h-3.5 text-brand dark:text-indigo-400']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
            </div>
            <h2 class="text-sm font-bold text-ink"><?php echo e(__('Actionable Insights')); ?></h2>
        </div>

        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $insights; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $insight): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                <div
                    <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'insight-'.e($insight['id']).''; ?>wire:key="insight-<?php echo e($insight['id']); ?>"
                    x-data="{ visible: true }"
                    x-show="visible"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-x-0"
                    x-transition:leave-end="opacity-0 -translate-x-4"
                >
                    <?php if (isset($component)) { $__componentOriginal40f255f8ae73c99b5cb7a51bd9267a16 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal40f255f8ae73c99b5cb7a51bd9267a16 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.insight-card','data' => ['icon' => $insight['icon'],'title' => $insight['title'],'description' => $insight['description'],'actionUrl' => $insight['actionUrl'],'actionLabel' => $insight['actionLabel'],'priority' => $insight['priority'],'insightId' => $insight['id'],'dismissable' => true,'xOn:click.self' => 'visible = false']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('insight-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($insight['icon']),'title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($insight['title']),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($insight['description']),'actionUrl' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($insight['actionUrl']),'actionLabel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($insight['actionLabel']),'priority' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($insight['priority']),'insightId' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($insight['id']),'dismissable' => true,'x-on:click.self' => 'visible = false']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal40f255f8ae73c99b5cb7a51bd9267a16)): ?>
<?php $attributes = $__attributesOriginal40f255f8ae73c99b5cb7a51bd9267a16; ?>
<?php unset($__attributesOriginal40f255f8ae73c99b5cb7a51bd9267a16); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal40f255f8ae73c99b5cb7a51bd9267a16)): ?>
<?php $component = $__componentOriginal40f255f8ae73c99b5cb7a51bd9267a16; ?>
<?php unset($__componentOriginal40f255f8ae73c99b5cb7a51bd9267a16); ?>
<?php endif; ?>
                </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>
    <?php else: ?>
        
        <div class="flex flex-col items-center justify-center py-8 px-4 bg-surface-2 rounded-2xl border border-border">
            <div class="relative w-14 h-14 mb-3">
                <div class="absolute inset-0 bg-success/10 dark:bg-green-900/20 rounded-full animate-pulse"></div>
                <div class="relative w-14 h-14 bg-success/15 dark:bg-green-900/30 rounded-full flex items-center justify-center">
                    <svg class="w-7 h-7 text-green-500 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
            </div>
            <p class="text-sm font-semibold text-ink"><?php echo e(__("Everything's looking great!")); ?></p>
            <p class="text-xs text-muted mt-1"><?php echo e(__('No actions needed right now.')); ?></p>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH /home/dahimail.com/public_html/resources/views/livewire/dashboard/actionable-insights.blade.php ENDPATH**/ ?>