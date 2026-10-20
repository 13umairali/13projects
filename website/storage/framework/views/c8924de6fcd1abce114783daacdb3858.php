<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => __('Contacts')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Contacts'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    
    <div x-data="{ loaded: false }" x-init="
        const observer = new MutationObserver((mutations) => {
            for (const m of mutations) {
                for (const node of m.addedNodes) {
                    if (node.nodeType === 1 && node.hasAttribute('wire:id')) {
                        loaded = true;
                        observer.disconnect();
                        return;
                    }
                }
            }
        });
        observer.observe($el, { childList: true, subtree: true });
        setTimeout(() => { loaded = true; observer.disconnect(); }, 5000);
    ">
        
        <div x-show="!loaded" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="space-y-6">
            
            <div class="flex items-center justify-between">
                <div>
                    <div class="h-8 w-36 bg-gray-200 dark:bg-gray-700 rounded-lg animate-pulse"></div>
                    <div class="h-4 w-56 bg-gray-200 dark:bg-gray-700 rounded mt-2 animate-pulse"></div>
                </div>
                <div class="h-10 w-36 bg-gray-200 dark:bg-gray-700 rounded-lg animate-pulse"></div>
            </div>

            
            <div class="flex items-center gap-3">
                <div class="h-10 flex-1 max-w-sm bg-gray-200 dark:bg-gray-700 rounded-lg animate-pulse"></div>
                <div class="h-10 w-28 bg-gray-200 dark:bg-gray-700 rounded-lg animate-pulse"></div>
                <div class="h-10 w-28 bg-gray-200 dark:bg-gray-700 rounded-lg animate-pulse"></div>
            </div>

            
            <div class="bg-surface-2  rounded-xl border border-border dark:border-gray-700 overflow-hidden animate-pulse">
                
                <div class="grid grid-cols-12 gap-4 px-6 py-3 border-b border-border dark:border-gray-700 bg-surface ">
                    <div class="col-span-1 h-4 w-4 bg-gray-200 dark:bg-gray-700 rounded"></div>
                    <div class="col-span-3 h-4 w-16 bg-gray-200 dark:bg-gray-700 rounded"></div>
                    <div class="col-span-3 h-4 w-12 bg-gray-200 dark:bg-gray-700 rounded"></div>
                    <div class="col-span-2 h-4 w-16 bg-gray-200 dark:bg-gray-700 rounded"></div>
                    <div class="col-span-2 h-4 w-12 bg-gray-200 dark:bg-gray-700 rounded"></div>
                    <div class="col-span-1 h-4 w-16 bg-gray-200 dark:bg-gray-700 rounded"></div>
                </div>
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($i = 0; $i < 8; $i++): ?>
                <div class="grid grid-cols-12 gap-4 px-6 py-4 border-b border-border dark:border-gray-700/50 items-center">
                    <div class="col-span-1 h-4 w-4 bg-gray-200 dark:bg-gray-700 rounded"></div>
                    <div class="col-span-3 flex items-center gap-3">
                        <div class="h-9 w-9 bg-gray-200 dark:bg-gray-700 rounded-full flex-shrink-0"></div>
                        <div class="space-y-1.5 flex-1">
                            <div class="h-3.5 w-28 bg-gray-200 dark:bg-gray-700 rounded"></div>
                            <div class="h-3 w-36 bg-gray-200 dark:bg-gray-700 rounded"></div>
                        </div>
                    </div>
                    <div class="col-span-3 h-3.5 w-24 bg-gray-200 dark:bg-gray-700 rounded"></div>
                    <div class="col-span-2 h-6 w-16 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
                    <div class="col-span-2 h-3.5 w-20 bg-gray-200 dark:bg-gray-700 rounded"></div>
                    <div class="col-span-1 h-8 w-8 bg-gray-200 dark:bg-gray-700 rounded-lg ml-auto"></div>
                </div>
                <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <div class="flex items-center justify-between">
                <div class="h-4 w-40 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
                <div class="flex gap-1">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($i = 0; $i < 5; $i++): ?>
                    <div class="h-9 w-9 bg-gray-200 dark:bg-gray-700 rounded-lg animate-pulse"></div>
                    <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>

        
        <div x-show="loaded" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-cloak>
            <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('contacts.contact-list', []);

$__keyOuter = $__key ?? null;

$__key = null;
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-1524043302-0', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key, $__componentSlots);

echo $__html;

unset($__html);
unset($__key);
$__key = $__keyOuter;
unset($__keyOuter);
unset($__name);
unset($__params);
unset($__componentSlots);
unset($__split);
?>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5863877a5171c196453bfa0bd807e410)): ?>
<?php $attributes = $__attributesOriginal5863877a5171c196453bfa0bd807e410; ?>
<?php unset($__attributesOriginal5863877a5171c196453bfa0bd807e410); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5863877a5171c196453bfa0bd807e410)): ?>
<?php $component = $__componentOriginal5863877a5171c196453bfa0bd807e410; ?>
<?php unset($__componentOriginal5863877a5171c196453bfa0bd807e410); ?>
<?php endif; ?>
<?php /**PATH /home/dahimail.com/public_html/resources/views/contacts/index.blade.php ENDPATH**/ ?>