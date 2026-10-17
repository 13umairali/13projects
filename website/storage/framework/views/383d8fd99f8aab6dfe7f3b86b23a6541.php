<div x-data="{ dragOver: false }" class="space-y-6">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
         class="p-3 bg-success/10 border border-success/20 text-success rounded-xl text-sm flex items-center justify-between">
        <span><?php echo e(session('success')); ?></span>
        <button @click="show = false" class="text-green-500 hover:text-success">&times;</button>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-ink"><?php echo e(__('Knowledge Base')); ?></h1>
            <p class="text-sm text-muted mt-0.5"><?php echo e(__('Train your AI with documents, websites, and Q&A pairs')); ?></p>
        </div>
    </div>

    
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-surface-2 rounded-xl border border-border p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-primary-50 dark:bg-primary-900/20 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div><p class="text-2xl font-bold text-ink"><?php echo e($stats['documents']); ?></p><p class="text-xs text-muted"><?php echo e(__('Documents')); ?></p></div>
            </div>
        </div>
        <div class="bg-surface-2 rounded-xl border border-border p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-cyan-50 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div><p class="text-2xl font-bold text-ink"><?php echo e($stats['qa_pairs']); ?></p><p class="text-xs text-muted"><?php echo e(__('Q&A Pairs')); ?></p></div>
            </div>
        </div>
        <div class="bg-surface-2 rounded-xl border border-border p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-violet-50 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9"/></svg>
                </div>
                <div><p class="text-2xl font-bold text-ink"><?php echo e($stats['websites']); ?></p><p class="text-xs text-muted"><?php echo e(__('URLs')); ?></p></div>
            </div>
        </div>
        <div class="bg-surface-2 rounded-xl border border-border p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-success/10 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                </div>
                <div><p class="text-2xl font-bold text-ink"><?php echo e(number_format($stats['total_chunks'])); ?></p><p class="text-xs text-muted"><?php echo e(__('Indexed Sections')); ?></p></div>
            </div>
        </div>
    </div>

    
    <div class="border-b border-border">
        <nav class="flex gap-6 -mb-px">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['documents' => __('Documents'), 'websites' => __('Websites'), 'qa' => __('Q&A Pairs')]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
            <button wire:click="$set('activeTab', '<?php echo e($key); ?>')" class="pb-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap <?php echo e($activeTab === $key ? 'border-primary-600 text-primary-700' : 'border-transparent text-muted hover:text-ink/80'); ?>"><?php echo e($label); ?></button>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </nav>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'documents'): ?>
    <div>
        <div @dragover.prevent="dragOver = true" @dragleave.prevent="dragOver = false"
             @drop.prevent="dragOver = false; $refs.docInput.files = $event.dataTransfer.files; $refs.docInput.dispatchEvent(new Event('change'))"
             :class="dragOver ? 'border-primary-400 bg-primary-50 dark:bg-primary-900/20' : 'border-border bg-surface'"
             class="border-2 border-dashed rounded-2xl p-8 text-center transition-colors mb-6 cursor-pointer hover:border-primary-300">
            <input type="file" wire:model="uploadFile" accept=".pdf,.docx,.txt,.csv,.xlsx,.md" class="hidden" x-ref="docInput">
            <div class="w-12 h-12 bg-surface  rounded-xl flex items-center justify-center mx-auto mb-3" :class="dragOver && 'bg-primary-100 dark:bg-primary-900/30'">
                <svg class="w-6 h-6 text-muted" :class="dragOver && 'text-primary-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
            </div>
            <button type="button" @click="$refs.docInput.click()" class="text-sm font-medium text-ink/80">
                <span x-show="!dragOver"><?php echo e(__('Drag and drop files here, or')); ?> <span class="text-primary-600"><?php echo e(__('browse')); ?></span></span>
                <span x-show="dragOver" class="text-primary-600"><?php echo e(__('Drop files to upload')); ?></span>
            </button>
            <p class="text-xs text-muted mt-1"><?php echo e(__('PDF, DOCX, TXT, CSV, XLSX (Max 25MB)')); ?></p>
            <div wire:loading wire:target="uploadFile" class="mt-3 inline-flex items-center gap-2 text-sm text-primary-600">
                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg> <?php echo e(__('Uploading...')); ?>

            </div>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['uploadFile'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-sm text-red-500 mb-4"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($documents->count() > 0): ?>
        
        <div class="bg-surface-2 rounded-2xl border border-border">
            <table class="w-full">
                <thead><tr class="bg-surface border-b border-border">
                    <th class="text-left text-xs font-semibold text-muted uppercase px-4 py-3"><?php echo e(__('Name')); ?></th>
                    <th class="text-left text-xs font-semibold text-muted uppercase px-4 py-3 hidden sm:table-cell"><?php echo e(__('Size')); ?></th>
                    <th class="text-left text-xs font-semibold text-muted uppercase px-4 py-3 hidden lg:table-cell"><?php echo e(__('Chunks')); ?></th>
                    <th class="text-left text-xs font-semibold text-muted uppercase px-4 py-3"><?php echo e(__('Status')); ?></th>
                    <th class="text-left text-xs font-semibold text-muted uppercase px-4 py-3 hidden md:table-cell"><?php echo e(__('Date')); ?></th>
                    <th class="w-12 px-4 py-3"></th>
                </tr></thead>
                <tbody class="divide-y divide-border/60">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $documents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                    <tr class="hover:bg-surface" <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'doc-'.e($doc->id).''; ?>wire:key="doc-<?php echo e($doc->id); ?>">
                        <td class="px-4 py-3"><div class="flex items-center gap-3">
                            <?php $tc = match($doc->file_type) { 'pdf' => 'bg-danger/15 text-danger', 'docx' => 'bg-info/15 text-blue-600', 'xlsx' => 'bg-success/15 text-success', default => 'bg-surface  text-muted ' }; ?>
                            <span class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold <?php echo e($tc); ?> flex-shrink-0"><?php echo e(strtoupper($doc->file_type ?? 'DOC')); ?></span>
                            <span class="text-sm font-medium text-ink truncate max-w-xs"><?php echo e($doc->title); ?></span>
                        </div></td>
                        <td class="px-4 py-3 text-sm text-muted hidden sm:table-cell"><?php echo e($doc->file_size ? ($doc->file_size > 1048576 ? round($doc->file_size/1048576,1).' MB' : round($doc->file_size/1024).' KB') : '--'); ?></td>
                        <td class="px-4 py-3 text-sm text-muted hidden lg:table-cell"><?php echo e($doc->chunks_count ?? 0); ?></td>
                        <td class="px-4 py-3">
                            <?php $sc = match($doc->status) { 'ready' => 'bg-success/15 text-success', 'failed' => 'bg-danger/15 text-danger', default => 'bg-warning/15 text-warning' }; ?>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium <?php echo e($sc); ?>">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($doc->status, ['uploading','extracting','chunking','embedding'])): ?><svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                <?php elseif($doc->status==='ready'): ?><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?> <?php echo e(ucfirst($doc->status)); ?>

                            </span>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($doc->error_message): ?><p class="text-xs text-red-500 mt-0.5 truncate max-w-[200px]" title="<?php echo e($doc->error_message); ?>"><?php echo e($doc->error_message); ?></p><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-sm text-muted hidden md:table-cell whitespace-nowrap"><?php echo e($doc->created_at->format('M j, Y')); ?></td>
                        <td class="px-4 py-3">
                            
                            <div x-data="{
                                    open: false,
                                    menuTop: 0,
                                    menuLeft: 0,
                                    reposition() {
                                        const r = this.$refs.trigger.getBoundingClientRect();
                                        this.menuLeft = r.right - 144; // menu width 144 (w-36)
                                        this.menuTop = r.bottom + 4;
                                    },
                                    toggle() {
                                        this.open = !this.open;
                                        if (this.open) this.$nextTick(() => this.reposition());
                                    },
                                }"
                                 @scroll.window="open && reposition()"
                                 @resize.window="open && reposition()"
                                 @keydown.escape.window="open = false">
                                <button type="button" x-ref="trigger" @click="toggle()" class="action-dots" aria-label="More actions" :aria-expanded="open">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01"/></svg>
                                </button>
                                <template x-teleport="body">
                                    <div x-show="open" @click.outside="open = false" x-transition
                                         :style="`position: fixed; top: ${menuTop}px; left: ${menuLeft}px; z-index: 9999;`"
                                         class="w-36 bg-surface-2 rounded-xl shadow-lg border border-border py-1"
                                         style="display:none;">
                                        <button wire:click="reprocessDocument(<?php echo e($doc->id); ?>)" @click="open = false" class="w-full text-left px-4 py-2 text-sm text-ink/80 hover:bg-surface"><?php echo e(__('Re-process')); ?></button>
                                        <button wire:click="deleteDocument(<?php echo e($doc->id); ?>)" wire:confirm="Delete this document? This action cannot be undone." @click="open = false" class="w-full text-left px-4 py-2 text-sm text-danger hover:bg-danger/10"><?php echo e(__('Delete')); ?></button>
                                    </div>
                                </template>
                            </div>
                        </td>
                    </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['type' => 'document','title' => __('No documents yet'),'description' => __('Upload files above to train your AI.'),'compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'document','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('No documents yet')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Upload files above to train your AI.')),'compact' => true]); ?>
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
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'websites'): ?>
    <div>
        <div class="bg-surface-2 rounded-2xl border border-border p-6 mb-6">
            <h3 class="text-sm font-semibold text-ink mb-3"><?php echo e(__('Add Website URL')); ?></h3>
            <form wire:submit="scrapeWebsite" class="flex gap-3">
                <div class="relative flex-1">
                    <svg class="w-4 h-4 text-muted absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3"/></svg>
                    <input type="url" wire:model="scrapeUrl" placeholder="https://example.com/docs" class="w-full pl-9 pr-4 py-2.5 text-sm bg-surface border border-border rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 placeholder-gray-400">
                </div>
                <button type="submit" class="btn-primary px-5 py-2.5 text-sm"><?php echo e(__('Scrape')); ?></button>
            </form>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['scrapeUrl'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-sm text-red-500 mt-2"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <div class="space-y-3">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $websites; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $site): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
            <div class="bg-surface-2 rounded-xl border border-border p-4 flex items-center justify-between" <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'site-'.e($site->id).''; ?>wire:key="site-<?php echo e($site->id); ?>">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-info/10 rounded-lg flex items-center justify-center"><svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3"/></svg></div>
                    <div>
                        <p class="text-sm font-medium text-ink"><?php echo e($site->source_url ?? $site->title); ?></p>
                        <p class="text-xs text-muted"><?php echo e($site->chunks_count ?? 0); ?> chunks &middot; <span title="<?php echo e($site->created_at->format('M j, Y')); ?>"><?php echo e($site->created_at->diffForHumans()); ?></span></p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <?php $sc = match($site->status) { 'ready' => 'bg-success/15 text-success', 'failed' => 'bg-danger/15 text-danger', default => 'bg-warning/15 text-warning' }; ?>
                    <span class="px-2 py-0.5 text-xs font-medium rounded-full <?php echo e($sc); ?>"><?php echo e(ucfirst($site->status)); ?></span>
                    <button wire:click="reprocessDocument(<?php echo e($site->id); ?>)" class="p-1.5 text-muted/60 hover:text-ink rounded-lg hover:bg-surface" :title="__('Re-scrape')"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg></button>
                    <button wire:click="deleteDocument(<?php echo e($site->id); ?>)" wire:confirm="Delete this website? This action cannot be undone." class="p-1.5 text-muted hover:text-red-500 rounded-lg hover:bg-danger/10"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                </div>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['type' => 'document','title' => __('No websites scraped'),'description' => __('Add a URL above to scrape website content.'),'compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'document','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('No websites scraped')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Add a URL above to scrape website content.')),'compact' => true]); ?>
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
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'qa'): ?>
    <div>
        <div class="bg-surface-2 rounded-2xl border border-border p-6 mb-6">
            <h3 class="text-sm font-semibold text-ink mb-3"><?php echo e(__('Add Q&A Pair')); ?></h3>
            <form wire:submit="addQAPair" class="space-y-3">
                <div><label class="block text-xs font-medium text-muted mb-1"><?php echo e(__('Question')); ?></label>
                    <input type="text" wire:model="qaQuestion" placeholder="<?php echo e(__('Enter a common question...')); ?>" class="w-full px-4 py-2.5 text-sm bg-surface border border-border rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 placeholder-gray-400">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['qaQuestion'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-500 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></div>
                <div><label class="block text-xs font-medium text-muted mb-1"><?php echo e(__('Answer')); ?></label>
                    <textarea rows="3" wire:model="qaAnswer" placeholder="<?php echo e(__('Provide the answer...')); ?>" class="w-full px-4 py-2.5 text-sm bg-surface border border-border rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 placeholder-gray-400 resize-none"></textarea>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['qaAnswer'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-500 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></div>
                <button type="submit" class="btn-primary px-5 py-2 text-sm"><?php echo e(__('Add Pair')); ?></button>
            </form>
        </div>
        <div class="space-y-3">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $qaPairs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $qa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
            <div class="bg-surface-2 rounded-xl border border-border p-4" <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'qa-'.e($qa->id).''; ?>wire:key="qa-<?php echo e($qa->id); ?>">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editingQaId === $qa->id): ?>
                <form wire:submit="saveEditQA" class="space-y-3">
                    <div><input type="text" wire:model="editQuestion" class="w-full px-3 py-2 text-sm border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['editQuestion'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-500 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></div>
                    <div><textarea rows="3" wire:model="editAnswer" class="w-full px-3 py-2 text-sm border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none"></textarea>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['editAnswer'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-red-500 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></div>
                    <div class="flex gap-2"><button type="submit" class="btn-primary px-3 py-1.5 text-sm"><?php echo e(__('Save')); ?></button><button type="button" wire:click="cancelEditQA" class="btn-secondary px-3 py-1.5 text-sm"><?php echo e(__('Cancel')); ?></button></div>
                </form>
                <?php else: ?>
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1"><span class="w-5 h-5 bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 rounded-md flex items-center justify-center text-xs font-bold flex-shrink-0">Q</span><p class="text-sm font-medium text-ink"><?php echo e($qa->question); ?></p></div>
                        <div class="flex items-start gap-2 mt-2 ml-7"><span class="w-5 h-5 bg-success/15 text-success rounded-md flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">A</span><p class="text-sm text-muted "><?php echo e($qa->answer); ?></p></div>
                    </div>
                    <div class="flex items-center gap-1 flex-shrink-0">
                        <button wire:click="startEditQA(<?php echo e($qa->id); ?>)" class="p-1.5 text-muted/60 hover:text-ink rounded-lg hover:bg-surface"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                        <button wire:click="deleteQAPair(<?php echo e($qa->id); ?>)" wire:confirm="Delete this Q&amp;A pair? This action cannot be undone." class="p-1.5 text-muted hover:text-red-500 rounded-lg hover:bg-danger/10"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                    </div>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['type' => 'document','title' => __('No Q&A pairs yet'),'description' => __('Add custom question-answer pairs to fine-tune responses.'),'compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'document','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('No Q&A pairs yet')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Add custom question-answer pairs to fine-tune responses.')),'compact' => true]); ?>
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
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH /home/dahimail.com/public_html/resources/views/livewire/knowledge-base/document-manager.blade.php ENDPATH**/ ?>