<div class="space-y-6">
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('success')): ?>
        <div class="bg-gradient-to-r from-green-50 to-emerald-50 border border-success/20 text-success px-4 py-3.5 rounded-xl text-sm font-medium flex items-center gap-3 shadow-sm"
             x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div class="w-8 h-8 bg-success/15 rounded-lg flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-ink"><?php echo e(__('Deals')); ?></h1>
            <p class="text-sm text-muted mt-0.5"><?php echo e(__('Track your sales pipeline and deal progress')); ?></p>
        </div>
        <div class="flex items-center gap-2">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->pipelines->isNotEmpty()): ?>
            <div x-data="{ pipeOpen: false, showNewForm: false, newName: '', submitPipeline(wire) { if (this.newName.trim()) { wire.createNewPipeline(this.newName); this.showNewForm = false; this.newName = ''; } } }" class="relative">
                <button @click="pipeOpen = !pipeOpen" class="flex items-center gap-2 text-sm bg-surface-2 border border-border rounded-xl px-4 py-2.5 hover:border-primary-300 transition-all">
                    <span class="font-medium text-ink"><?php echo e($this->pipelines->firstWhere('id', $activePipelineId)?->name ?? __('Pipeline')); ?></span>
                    <svg class="w-4 h-4 text-muted" :class="pipeOpen && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="pipeOpen" @click.away="pipeOpen = false" x-transition class="absolute right-0 mt-1 w-64 bg-surface-2 rounded-xl shadow-2xl border border-border py-1 z-50" style="display:none">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->pipelines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                    <button wire:click="switchPipeline(<?php echo e($p->id); ?>)" @click="pipeOpen=false" class="w-full text-left px-4 py-2 text-sm hover:bg-surface flex items-center justify-between <?php echo e($p->id == $activePipelineId ? 'text-brand font-semibold bg-brand/5' : 'text-ink/80'); ?>">
                        <span><?php echo e($p->name); ?></span>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($p->id == $activePipelineId): ?><svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </button>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <div class="border-t border-border my-1"></div>
                    <button @click="pipeOpen=false;showNewForm=true" class="w-full text-left px-4 py-2 text-sm text-brand hover:bg-brand/5 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <?php echo e(__('New Pipeline')); ?>

                    </button>
                </div>
                
                <div x-show="showNewForm" x-transition @click.away="showNewForm=false" class="absolute right-0 mt-1 w-72 bg-surface-2 rounded-xl shadow-2xl border border-border p-4 z-50" style="display:none">
                    <p class="text-sm font-semibold text-ink mb-3"><?php echo e(__('Create New Pipeline')); ?></p>
                    <input x-ref="pipeInput" x-model="newName" @keydown.enter="submitPipeline($wire)" type="text" placeholder="<?php echo e(__('Pipeline name...')); ?>" class="w-full px-3 py-2 text-sm border border-border rounded-xl bg-surface focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 mb-3" x-init="$watch('showNewForm', v => { if(v) setTimeout(() => $refs.pipeInput.focus(), 100) })">
                    <div class="flex gap-2">
                        <button @click="submitPipeline($wire)" class="btn-primary flex-1 px-3 py-2 text-sm"><?php echo e(__('Create')); ?></button>
                        <button @click="showNewForm=false;newName=''" class="px-3 py-2 text-sm text-muted border border-border rounded-xl hover:bg-surface"><?php echo e(__('Cancel')); ?></button>
                    </div>
                </div>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <div class="flex items-center bg-surface-2 border border-border rounded-xl p-0.5">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['open' => __('Open'), 'won' => __('Won'), 'lost' => __('Lost'), 'all' => __('All')]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fKey => $fLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                <button wire:click="$set('statusFilter', '<?php echo e($fKey); ?>')"
                        class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all duration-200 <?php echo e($statusFilter === $fKey ? 'bg-primary-600 text-white shadow-sm' : 'text-muted  hover:text-ink hover:bg-surface'); ?>">
                    <?php echo e($fLabel); ?>

                </button>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->stages->isNotEmpty()): ?>
    
    <div class="grid gap-3" style="grid-template-columns: repeat(<?php echo e(min($this->stages->count(), 6)); ?>, minmax(0, 1fr));">

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->stages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
        <div class="bg-surface-2 rounded-xl border border-border p-4 text-center hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 relative overflow-hidden group">
            
            <div class="absolute top-0 left-0 right-0 h-1 rounded-t-xl" style="background-color: <?php echo e($stage->color); ?>"></div>
            <div class="flex items-center justify-center gap-1.5 mb-1.5 mt-1">
                <span class="w-2.5 h-2.5 rounded-full" style="background-color: <?php echo e($stage->color); ?>"></span>
                <span class="text-xs font-bold text-muted uppercase tracking-wider"><?php echo e($stage->name); ?></span>
            </div>
            <p class="text-xl font-bold text-ink flex items-center justify-center gap-1">
                <svg class="w-4 h-4 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <?php echo \App\Helpers\CurrencyHelper::display($stage->total_value); ?>
            </p>
            <p class="text-xs text-muted mt-0.5"><?php echo e($stage->deal_count); ?> <?php echo e($stage->deal_count === 1 ? __('deal') : __('deals')); ?></p>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->stages->every(fn ($s) => $s->deal_count === 0)): ?>
    <div class="bg-brand/5 border border-brand/20 rounded-xl px-5 py-4 flex items-start gap-3" x-data="{ show: true }" x-show="show">
        <svg class="w-5 h-5 text-brand shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <div class="flex-1">
            <p class="text-sm font-semibold text-ink"><?php echo e(__('Getting started with Deals')); ?></p>
            <p class="text-xs text-muted mt-1"><?php echo e(__('Click')); ?> <strong>"+ <?php echo e(__('Add deal')); ?>"</strong> <?php echo e(__('in any stage column to create your first deal. You can drag deals between stages, mark them as Won/Lost, and track your pipeline value.')); ?></p>
        </div>
        <button @click="show = false" class="text-muted hover:text-ink shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div class="overflow-x-auto -mx-6 px-6 pb-4">
        <div class="flex gap-4 min-w-max">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->stages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
            <?php
                $stageSlug = \Illuminate\Support\Str::slug($stage->name);
                $isWon = $stageSlug === 'won' || $stageSlug === 'closed-won';
                $isLost = $stageSlug === 'lost' || $stageSlug === 'closed-lost';
            ?>
            <div class="w-80 flex-shrink-0" <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'stage-'.e($stage->id).''; ?>wire:key="stage-<?php echo e($stage->id); ?>">
                
                <div class="flex items-center justify-between mb-3 px-1">
                    <div class="flex items-center gap-2.5">
                        <span class="w-3.5 h-3.5 rounded-lg shadow-sm" style="background-color: <?php echo e($stage->color); ?>"></span>
                        <h3 class="text-sm font-bold text-ink/80"><?php echo e($stage->name); ?></h3>
                        <span class="text-xs font-bold text-muted bg-surface  px-2 py-0.5 rounded-full border border-border/50"><?php echo e($stage->deal_count); ?></span>
                    </div>
                    <span class="text-xs font-bold text-muted bg-surface px-2.5 py-1 rounded-lg border border-border/50">
                        <svg class="w-3 h-3 text-muted inline mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <?php echo \App\Helpers\CurrencyHelper::display($stage->total_value); ?>
                    </span>
                </div>

                
                <div class="space-y-3 rounded-xl p-2 min-h-[120px] transition-all duration-150
                    <?php echo e($isWon ? 'bg-success/5 border border-dashed border-success/20' : ($isLost ? 'bg-danger/5 border border-dashed border-danger/20' : 'bg-surface')); ?>"
                     x-data="{ dragOver: false, dragCount: 0 }"
                     x-on:dragenter.prevent="dragCount++; dragOver = true"
                     x-on:dragover.prevent
                     x-on:dragleave.prevent="dragCount--; if(dragCount<=0){dragOver=false;dragCount=0}"
                     x-on:drop.prevent="dragOver=false;dragCount=0;let id=parseInt($event.dataTransfer.getData('text/plain'));if(id)$wire.updateDealStage(id,<?php echo e($stage->id); ?>)"
                     :class="dragOver && 'ring-2 ring-dashed ring-brand/40 bg-brand/5'">

                    
                    <div x-show="dragOver" x-transition class="flex items-center justify-center py-3 text-brand text-xs font-semibold gap-1.5 rounded-lg border-2 border-dashed border-brand/30 bg-brand/5 mb-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                        <?php echo e(__('Drop here')); ?> — <?php echo e($stage->name); ?>

                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $stage->loaded_deals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $deal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                    <div class="bg-surface-2 rounded-xl border border-border shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 cursor-grab active:cursor-grabbing group overflow-hidden" <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'deal-'.e($deal->id).''; ?>wire:key="deal-<?php echo e($deal->id); ?>"
                         draggable="true"
                         x-data="{ isDragging: false }"
                         x-on:dragstart="isDragging=true;$event.dataTransfer.setData('text/plain','<?php echo e($deal->id); ?>');$event.dataTransfer.effectAllowed='move'"
                         x-on:dragend="isDragging=false"
                         :class="isDragging && 'opacity-40 scale-95 rotate-1'">
                        
                        <div class="border-l-[3px] p-4" style="border-left-color: <?php echo e($stage->color); ?>">
                            <div class="flex items-start justify-between mb-2">
                                <button wire:click="expandDeal(<?php echo e($deal->id); ?>)" class="text-sm font-bold text-ink group-hover:text-primary-700 dark:group-hover:text-primary-300 transition-colors text-left leading-snug"><?php echo e($deal->title); ?></button>
                                <div x-data="{ open: false, mStyle: '' }" class="relative">
                                    <button @click="
                                        let r = $el.getBoundingClientRect();
                                        let h = window.innerHeight;
                                        let top = r.bottom + 4;
                                        let left = r.right - 200;
                                        let maxH = h - top - 8;
                                        if (maxH < 200) { top = Math.max(8, r.top - 300); maxH = r.top - 16; }
                                        if (left < 8) left = 8;
                                        mStyle = 'top:'+top+'px;left:'+left+'px;max-height:'+Math.max(200,maxH)+'px';
                                        open = !open;
                                    " class="action-dots opacity-0 group-hover:opacity-100" aria-label="More actions" :aria-expanded="open">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01"/></svg>
                                    </button>
                                    <template x-teleport="body">
                                    <div x-show="open" @click.away="open = false" @scroll.window="open = false" x-transition class="fixed w-52 bg-surface-2 rounded-xl shadow-2xl border border-border py-1.5 z-[9999] overflow-y-auto" :style="mStyle" style="display: none;">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->stages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ts): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($ts->id !== $stage->id): ?>
                                            <button wire:click="updateDealStage(<?php echo e($deal->id); ?>, <?php echo e($ts->id); ?>)" @click="open = false" class="w-full text-left px-4 py-1.5 text-sm text-ink/80 hover:bg-surface flex items-center gap-2.5 transition-colors">
                                                <span class="w-2.5 h-2.5 rounded-full" style="background-color: <?php echo e($ts->color); ?>"></span>
                                                <?php echo e(__('Move to')); ?> <?php echo e($ts->name); ?>

                                            </button>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        <div class="border-t border-border my-1"></div>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($deal->status === 'open'): ?>
                                        <button wire:click="markAsWon(<?php echo e($deal->id); ?>)" @click="open = false" class="w-full text-left px-4 py-1.5 text-sm text-success hover:bg-success/10 flex items-center gap-2 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <?php echo e(__('Mark as Won')); ?>

                                        </button>
                                        <button wire:click="markAsLost(<?php echo e($deal->id); ?>)" @click="open = false" class="w-full text-left px-4 py-1.5 text-sm text-orange-600 hover:bg-orange-50 flex items-center gap-2 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <?php echo e(__('Mark as Lost')); ?>

                                        </button>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <button wire:click="deleteDeal(<?php echo e($deal->id); ?>)" wire:confirm="Delete this deal? This action cannot be undone." @click="open = false" class="w-full text-left px-4 py-1.5 text-sm text-danger hover:bg-danger/10 flex items-center gap-2 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            <?php echo e(__('Delete')); ?>

                                        </button>
                                    </div>
                                    </template>
                                </div>
                            </div>
                            <p class="text-xs text-muted mb-3"><?php echo e($deal->contact?->company ?? $deal->contact?->full_name ?? __('No contact')); ?></p>
                            <div class="flex items-center justify-between">
                                <span class="text-base font-bold text-ink flex items-center gap-1">
                                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <?php echo \App\Helpers\CurrencyHelper::display($deal->value); ?>
                                </span>
                                <div class="flex items-center gap-2">
                                    <?php $daysInStage = intval($deal->updated_at->diffInDays(now())); ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($daysInStage > 0): ?>
                                    <span class="text-xs text-muted flex items-center gap-1 bg-surface px-1.5 py-0.5 rounded">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <?php echo e($daysInStage); ?>d
                                    </span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($deal->assignedTo): ?>
                                    <div class="w-7 h-7 bg-gradient-to-br from-primary-500 to-primary-600 rounded-full flex items-center justify-center text-white text-xs font-bold ring-2 ring-white shadow-sm" title="<?php echo e($deal->assignedTo->name); ?>"><?php echo e($deal->assignedTo->initials); ?></div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($deal->status !== 'open'): ?>
                            <div class="mt-2.5 pt-2 border-t border-border">
                                <span class="text-xs font-bold px-2.5 py-0.5 rounded-full <?php echo e($deal->status === 'won' ? 'bg-success/15 text-success border border-success/20' : 'bg-danger/15 text-danger border border-danger/20'); ?>"><?php echo e(ucfirst($deal->status)); ?></span>
                            </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($addingToStageId === $stage->id): ?>
                    <div class="bg-surface-2 rounded-xl border-2 border-primary-300 p-4 space-y-3 shadow-sm">
                        <input type="text" wire:model="newDealTitle" placeholder="<?php echo e(__('Deal title... (e.g. Acme Corp Website)')); ?>" class="w-full px-4 py-2.5 text-sm border border-border rounded-xl bg-surface focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all duration-200" autofocus>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['newDealTitle'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-muted font-medium">$</span>
                            <input type="number" wire:model="newDealValue" placeholder="0" step="0.01" min="0" class="w-full pl-7 pr-4 py-2.5 text-sm border border-border rounded-xl bg-surface focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all duration-200">
                        </div>
                        <select wire:model="newDealContactId" class="w-full px-4 py-2.5 text-sm border border-border rounded-xl bg-surface focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all duration-200">
                            <option value=""><?php echo e(__('No contact')); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->contacts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                            <option value="<?php echo e($c->id); ?>"><?php echo e($c->first_name); ?> <?php echo e($c->last_name); ?> <?php echo e($c->company ? "({$c->company})" : ''); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                        <div class="flex items-center gap-2">
                            <button wire:click="createDeal" class="btn-primary flex-1 px-4 py-2 text-sm font-semibold"><?php echo e(__('Add')); ?></button>
                            <button wire:click="cancelAddDeal" class="px-4 py-2 text-sm text-muted  border border-border rounded-xl hover:bg-surface transition-all duration-200"><?php echo e(__('Cancel')); ?></button>
                        </div>
                    </div>
                    <?php else: ?>
                    <button wire:click="startAddDeal(<?php echo e($stage->id); ?>)" class="w-full py-3 border-2 border-dashed border-border rounded-xl text-sm text-muted hover:text-primary-600 dark:hover:text-primary-400 hover:border-primary-400 hover:bg-primary-50/50 dark:hover:bg-primary-900/20 transition-all duration-200 flex items-center justify-center gap-2 group">
                        <div class="w-6 h-6 bg-surface  group-hover:bg-primary-100 rounded-full flex items-center justify-center transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </div>
                        <?php echo e(__('Add deal')); ?>

                    </button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>
    </div>
    <?php else: ?>
    <div class="flex flex-col items-center justify-center py-20 text-center">
        <div class="w-20 h-20 rounded-2xl bg-surface flex items-center justify-center mb-5">
            <svg class="w-10 h-10 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
        </div>
        <h3 class="text-lg font-semibold text-ink mb-1"><?php echo e(__('No pipeline found')); ?></h3>
        <p class="text-sm text-muted mb-5 max-w-xs"><?php echo e(__('Create a pipeline with stages to start tracking your deals and revenue.')); ?></p>
        <button wire:click="createDefaultPipeline"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand text-white text-sm font-medium rounded-xl hover:bg-brand-strong transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <?php echo e(__('Create Sales Pipeline')); ?>

        </button>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($expandedDealId && $this->expandedDeal): ?>
    <?php $deal = $this->expandedDeal; ?>
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true" role="dialog" aria-labelledby="deal-detail-modal-title">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-surface/60 backdrop-blur-sm" wire:click="expandDeal(null)"></div>
            <div class="relative bg-surface-2 rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden"
                 x-data x-trap="true" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                
                <div class="bg-gradient-to-r from-primary-600 to-primary-700 px-6 py-4 flex items-center justify-between">
                    <h2 id="deal-detail-modal-title" class="text-lg font-bold text-white"><?php echo e($deal->title); ?></h2>
                    <button wire:click="expandDeal(null)" class="p-1.5 text-white/70 hover:text-white rounded-lg hover:bg-surface/10 transition-all duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 space-y-5">
                    <div class="grid grid-cols-2 gap-5">
                        <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-4 border border-green-100">
                            <p class="text-xs text-success font-semibold uppercase tracking-wider"><?php echo e(__('Value')); ?></p>
                            <p class="text-2xl font-bold text-ink mt-1 flex items-center gap-1">
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <?php echo \App\Helpers\CurrencyHelper::display($deal->value); ?>
                            </p>
                        </div>
                        <div class="bg-gradient-to-br from-gray-50 to-gray-100/50 rounded-xl p-4 border border-border/80">
                            <p class="text-xs text-muted font-semibold uppercase tracking-wider"><?php echo e(__('Status')); ?></p>
                            <div class="mt-2">
                                <span class="text-sm font-bold px-3 py-1 rounded-full <?php echo e(match($deal->status) { 'won' => 'bg-success/15 text-success border border-success/20', 'lost' => 'bg-danger/15 text-danger border border-danger/20', default => 'bg-info/15 text-info border border-info/20' }); ?>"><?php echo e(ucfirst($deal->status)); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-5">
                        <div>
                            <p class="text-xs text-muted font-semibold uppercase tracking-wider"><?php echo e(__('Stage')); ?></p>
                            <div class="flex items-center gap-2 mt-1.5">
                                <span class="w-3 h-3 rounded-full shadow-sm" style="background-color: <?php echo e($deal->dealStage?->color); ?>"></span>
                                <span class="text-sm font-semibold text-ink"><?php echo e($deal->dealStage?->name); ?></span>
                            </div>
                        </div>
                        <div>
                            <p class="text-xs text-muted font-semibold uppercase tracking-wider"><?php echo e(__('Pipeline')); ?></p>
                            <p class="text-sm font-semibold text-ink mt-1.5"><?php echo e($deal->pipeline?->name); ?></p>
                        </div>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($deal->contact): ?>
                    <div class="bg-surface rounded-xl p-4 border border-border">
                        <p class="text-xs text-muted font-semibold uppercase tracking-wider mb-1.5"><?php echo e(__('Contact')); ?></p>
                        <p class="text-sm font-semibold text-ink"><?php echo e($deal->contact->full_name); ?></p>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($deal->contact->company): ?><p class="text-xs text-muted mt-0.5"><?php echo e($deal->contact->company); ?></p><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($deal->assignedTo): ?>
                    <div>
                        <p class="text-xs text-muted font-semibold uppercase tracking-wider"><?php echo e(__('Assigned To')); ?></p>
                        <div class="flex items-center gap-2 mt-1.5">
                            <div class="w-6 h-6 bg-gradient-to-br from-primary-500 to-primary-600 rounded-full flex items-center justify-center text-white text-xs font-bold"><?php echo e($deal->assignedTo->initials); ?></div>
                            <p class="text-sm font-semibold text-ink"><?php echo e($deal->assignedTo->name); ?></p>
                        </div>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($deal->expected_close_date): ?>
                    <div>
                        <p class="text-xs text-muted font-semibold uppercase tracking-wider"><?php echo e(__('Expected Close')); ?></p>
                        <p class="text-sm font-semibold text-ink mt-1"><?php echo e($deal->expected_close_date->format('M j, Y')); ?></p>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($deal->notes): ?>
                    <div>
                        <p class="text-xs text-muted font-semibold uppercase tracking-wider"><?php echo e(__('Notes')); ?></p>
                        <p class="text-sm text-muted  mt-1 leading-relaxed"><?php echo e($deal->notes); ?></p>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <div class="text-xs text-muted flex items-center gap-3">
                        <span><?php echo e(__('Created')); ?> <?php echo e($deal->created_at->format('M j, Y')); ?></span>
                        <span class="w-1 h-1 bg-gray-300 dark:bg-gray-600 rounded-full"></span>
                        <span title="<?php echo e($deal->updated_at->format('M j, Y g:i A')); ?>"><?php echo e(__('Updated')); ?> <?php echo e($deal->updated_at->diffForHumans()); ?></span>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($deal->status === 'open'): ?>
                    <div class="flex items-center gap-2 pt-3 border-t border-border">
                        <button wire:click="markAsWon(<?php echo e($deal->id); ?>)" class="flex-1 px-4 py-2.5 bg-gradient-to-r from-green-600 to-emerald-600 text-white text-sm font-semibold rounded-xl hover:from-green-700 hover:to-emerald-700 shadow-sm hover:shadow-md transition-all duration-200 flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <?php echo e(__('Won')); ?>

                        </button>
                        <button wire:click="markAsLost(<?php echo e($deal->id); ?>)" class="flex-1 px-4 py-2.5 bg-gradient-to-r from-orange-500 to-orange-600 text-white text-sm font-semibold rounded-xl hover:from-orange-600 hover:to-orange-700 shadow-sm hover:shadow-md transition-all duration-200 flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <?php echo e(__('Lost')); ?>

                        </button>
                        <button wire:click="deleteDeal(<?php echo e($deal->id); ?>)" wire:confirm="Delete this deal? This action cannot be undone." class="px-4 py-2.5 text-sm text-danger border border-danger/20 rounded-xl hover:bg-danger/10 transition-all duration-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH /home/dahimail.com/public_html/resources/views/livewire/deals/deal-pipeline.blade.php ENDPATH**/ ?>