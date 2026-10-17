
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'icon' => 'sparkles',
    'title' => '',
    'description' => '',
    'actionUrl' => '#',
    'actionLabel' => 'Take Action',
    'priority' => 'suggestion',
    'dismissable' => true,
    'insightId' => '',
]));

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

foreach (array_filter(([
    'icon' => 'sparkles',
    'title' => '',
    'description' => '',
    'actionUrl' => '#',
    'actionLabel' => 'Take Action',
    'priority' => 'suggestion',
    'dismissable' => true,
    'insightId' => '',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $borderColor = match ($priority) {
        'urgent'    => 'border-l-red-500 dark:border-l-red-400',
        'attention' => 'border-l-amber-500 dark:border-l-amber-400',
        'suggestion'=> 'border-l-blue-500 dark:border-l-blue-400',
        'positive'  => 'border-l-green-500 dark:border-l-green-400',
        default     => 'border-l-blue-500 dark:border-l-blue-400',
    };

    $iconBg = match ($priority) {
        'urgent'    => 'bg-danger/10 dark:bg-red-900/30',
        'attention' => 'bg-warning/10 dark:bg-amber-900/30',
        'suggestion'=> 'bg-info/10 dark:bg-blue-900/30',
        'positive'  => 'bg-success/10 dark:bg-green-900/30',
        default     => 'bg-info/10 dark:bg-blue-900/30',
    };

    $iconColor = match ($priority) {
        'urgent'    => 'text-red-600 dark:text-red-400',
        'attention' => 'text-amber-600 dark:text-amber-400',
        'suggestion'=> 'text-blue-600 dark:text-blue-400',
        'positive'  => 'text-green-600 dark:text-green-400',
        default     => 'text-blue-600 dark:text-blue-400',
    };
?>

<div <?php echo e($attributes->merge([
    'class' => "relative bg-surface-2 rounded-xl border border-border border-l-4 {$borderColor} p-4 shadow-sm hover:shadow-md transition-all duration-200 group"
])); ?>>
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($dismissable && $insightId): ?>
        <button
            wire:click="dismiss('<?php echo e($insightId); ?>')"
            aria-label="Dismiss insight"
            class="absolute top-2.5 right-2.5 p-1 rounded-lg text-muted hover:text-ink hover:bg-surface transition-colors opacity-0 group-hover:opacity-100 focus:opacity-100"
        >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="flex items-start gap-3">
        
        <div class="flex-shrink-0 w-9 h-9 rounded-lg <?php echo e($iconBg); ?> flex items-center justify-center">
            <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => $icon,'class' => 'w-4.5 h-4.5 '.e($iconColor).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($icon),'class' => 'w-4.5 h-4.5 '.e($iconColor).'']); ?>
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

        
        <div class="flex-1 min-w-0 pr-4">
            <h4 class="text-sm font-semibold text-ink leading-tight"><?php echo e($title); ?></h4>
            <p class="text-xs text-muted mt-1 leading-relaxed"><?php echo e($description); ?></p>

            
            <a href="<?php echo e($actionUrl); ?>" wire:navigate class="inline-flex items-center gap-1.5 mt-3 px-3 py-1.5 text-xs font-semibold text-white bg-brand rounded-lg hover:bg-brand-strong transition-colors shadow-sm">
                <?php echo e($actionLabel); ?>

                <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'arrow-right','class' => 'w-3 h-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'arrow-right','class' => 'w-3 h-3']); ?>
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
            </a>
        </div>
    </div>
</div>
<?php /**PATH /home/dahimail.com/public_html/resources/views/components/insight-card.blade.php ENDPATH**/ ?>