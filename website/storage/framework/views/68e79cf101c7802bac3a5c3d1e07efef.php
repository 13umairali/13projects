<div class="flex flex-col lg:flex-row gap-6">
    
    <aside class="lg:w-56 flex-shrink-0">
        <div class="panel p-4 lg:sticky lg:top-24 space-y-1">
            <h3 class="text-xs font-semibold text-muted uppercase tracking-wider px-3 mb-2"><?php echo e(__('Categories')); ?></h3>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                <?php $count = \App\Models\HelpArticle::published()->category($key)->count(); ?>
                <button wire:click="selectCategory('<?php echo e($key); ?>')"
                        class="w-full flex items-center justify-between px-3 py-2 text-sm rounded-xl transition-colors
                               <?php echo e($activeCategory === $key ? 'bg-brand/10 text-brand font-medium' : 'text-ink/70 hover:bg-surface hover:text-ink'); ?>">
                    <span class="flex items-center gap-2">
                        <i data-lucide="<?php echo e($cat['icon']); ?>" class="w-4 h-4"></i>
                        <span><?php echo e($cat['label']); ?></span>
                    </span>
                    <span class="text-xs text-muted bg-surface px-1.5 py-0.5 rounded-full"><?php echo e($count); ?></span>
                </button>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>
    </aside>

    
    <div class="flex-1 min-w-0">
        
        <div class="mb-6">
            <div class="relative">
                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input type="text" wire:model.live.debounce.300ms="search"
                       class="w-full pl-10 pr-4 py-3 text-sm bg-surface-2 border border-border rounded-xl focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-transparent"
                       placeholder="<?php echo e(__('Search help articles...')); ?>">
            </div>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->selectedArticle): ?>
            
            <div class="panel p-6 sm:p-8">
                <button wire:click="back" class="flex items-center gap-1.5 text-sm text-muted hover:text-ink transition-colors mb-4">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
                    <?php echo e(__('Back to articles')); ?>

                </button>

                <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full bg-brand/10 text-brand mb-3">
                    <?php echo e($this->categories[$this->selectedArticle->category]['label'] ?? $this->selectedArticle->category); ?>

                </span>

                <h1 class="text-2xl font-bold text-ink mb-4"><?php echo e($this->selectedArticle->title); ?></h1>

                <div class="prose prose-sm max-w-none text-ink/80 leading-relaxed space-y-4">
                    <?php echo nl2br(e($this->selectedArticle->content)); ?>

                </div>

                
                <div class="mt-8 pt-6 border-t border-border">
                    <p class="text-sm font-medium text-ink mb-3"><?php echo e(__('Was this article helpful?')); ?></p>
                    <div class="flex items-center gap-3">
                        <button wire:click="markHelpful(<?php echo e($this->selectedArticle->id); ?>)"
                                class="btn-secondary text-xs gap-1.5">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3H14z"/><path d="M7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/></svg>
                            Yes (<?php echo e($this->selectedArticle->helpful_count); ?>)
                        </button>
                        <button wire:click="markNotHelpful(<?php echo e($this->selectedArticle->id); ?>)"
                                class="btn-secondary text-xs gap-1.5">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 15v4a3 3 0 0 0 3 3l4-9V2H5.72a2 2 0 0 0-2 1.7l-1.38 9a2 2 0 0 0 2 2.3H10z"/><path d="M17 2h3a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2h-3"/></svg>
                            No (<?php echo e($this->selectedArticle->not_helpful_count); ?>)
                        </button>
                    </div>
                </div>
            </div>
        <?php else: ?>
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->articles->isEmpty()): ?>
                <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['type' => 'search','title' => __('No articles found'),'description' => __('Try a different search term or browse by category.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'search','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('No articles found')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Try a different search term or browse by category.'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $attributes = $__attributesOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__attributesOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $component = $__componentOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__componentOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?>
            <?php else: ?>
                <div class="space-y-3">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->articles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $article): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                        <button wire:click="selectArticle(<?php echo e($article->id); ?>)"
                                class="w-full text-left panel p-5 hover:shadow-md transition-shadow group">
                            <div class="flex items-start gap-4">
                                <div class="flex-1 min-w-0">
                                    <span class="inline-flex px-2 py-0.5 text-[10px] font-medium rounded-full bg-brand/10 text-brand mb-1.5">
                                        <?php echo e($this->categories[$article->category]['label'] ?? $article->category); ?>

                                    </span>
                                    <h3 class="text-sm font-semibold text-ink group-hover:text-brand transition-colors"><?php echo e($article->title); ?></h3>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($article->excerpt): ?>
                                        <p class="text-xs text-muted mt-1 line-clamp-2"><?php echo e($article->excerpt); ?></p>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <svg class="w-4 h-4 text-muted group-hover:text-brand transition-colors shrink-0 mt-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                            </div>
                        </button>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php /**PATH /home/dahimail.com/public_html/resources/views/livewire/help-center.blade.php ENDPATH**/ ?>