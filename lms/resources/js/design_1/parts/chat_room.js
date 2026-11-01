/**
 * Course Chat Room JavaScript
 * 
 * Configuration must be set before loading this script:
 * window.chatRoomConfig = {
 *   courseSlug: string,
 *   currentUserId: number,
 *   isInstructor: boolean,
 *   translations: { ... }
 * }
 */

(function () {
    const config = window.chatRoomConfig || {};
    const courseSlug = config.courseSlug;
    const currentUserId = config.currentUserId;
    const isInstructor = config.isInstructor;
    const translations = config.translations || {};

    const messagesUrl = '/course/' + courseSlug + '/chat-room/messages';
    const sendUrl = '/course/' + courseSlug + '/chat-room/send';
    const editUrl = '/course/' + courseSlug + '/chat-room/edit/';
    const deleteUrl = '/course/' + courseSlug + '/chat-room/delete/';
    const clearUrl = '/course/' + courseSlug + '/chat-room/clear';
    const toggleUrl = '/course/' + courseSlug + '/chat-room/toggle';
    const typingUrl = '/course/' + courseSlug + '/chat-room/typing';

    const chatMessagesContainer = document.getElementById('chatMessagesContainer');
    const chatEmptyState = document.getElementById('chatEmptyState');
    const messageInput = document.getElementById('messageInput');
    const sendMessageBtn = document.getElementById('sendMessageBtn');
    const replyPreview = document.getElementById('replyPreview');
    const replyPreviewText = document.getElementById('replyPreviewText');
    const cancelReplyBtn = document.getElementById('cancelReply');
    const onlineUsersList = document.getElementById('onlineUsersList');
    const onlineUsersCount = document.getElementById('onlineUsersCount');
    const chatInputFooter = document.getElementById('chatInputFooter');
    const chatInputWrapper = document.getElementById('chatInputWrapper');
    const chatClosedNotice = document.getElementById('chatClosedNotice');
    const chatToolsDropdown = document.getElementById('chatToolsDropdown');
    const chatToolsBtn = document.getElementById('chatToolsBtn');
    const toggleChatBtn = document.getElementById('toggleChatBtn');
    const toggleChatText = document.getElementById('toggleChatText');
    const clearMessagesBtn = document.getElementById('clearMessagesBtn');

    // Video modal elements
    const videoModal = document.getElementById('videoModal');
    const videoModalPlayer = document.getElementById('videoModalPlayer');
    const videoModalClose = document.getElementById('videoModalClose');
    const videoModalCloseBtn = document.getElementById('videoModalCloseBtn');

    let replyToId = null;
    let lastMessageId = 0;
    let isLoading = false;
    let isChatClosed = false;
    let lastTypingSent = 0;
    let typingUserIds = [];

    // Toggle tools dropdown
    if (chatToolsBtn) {
        chatToolsBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            chatToolsDropdown.classList.toggle('active');
        });

        document.addEventListener('click', function () {
            chatToolsDropdown.classList.remove('active');
        });
    }



    function createMessageElement(message) {
        // System message
        if (message.is_system) {
            const systemDiv = document.createElement('div');
            systemDiv.className = 'chat-system-message';
            systemDiv.innerHTML = `<div class="chat-system-message__content">${escapeHtml(message.message)}</div>`;
            return systemDiv;
        }

        const isMine = message.sender_id === currentUserId;
        const messageDiv = document.createElement('div');
        messageDiv.className = 'chat-message' + (isMine ? ' chat-message--mine' : '');
        messageDiv.dataset.id = message.id;

        let replyHtml = '';
        if (message.reply_to) {
            let replyContent = '';
            if (message.reply_to.type === 'image' && message.reply_to.file_path) {
                replyContent = `<img src="${message.reply_to.file_path}" alt="Image" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px; vertical-align: middle;">`;
            } else {
                replyContent = escapeHtml((message.reply_to.message || '').substring(0, 50)) + (message.reply_to.message && message.reply_to.message.length > 50 ? '...' : '');
            }
            replyHtml = `
                <div class="chat-message__reply-preview">
                    <strong>${message.reply_to.sender ? message.reply_to.sender.full_name : 'User'}:</strong>
                    ${replyContent}
                </div>
            `;
        }

        // Edited indicator
        const editedHtml = message.edited_at ? `<span class="chat-message__edited">(${translations.edited || 'edited'})</span>` : '';

        // Generate message content based on type
        let messageContent = '';
        const type = message.type || 'text';

        if (type === 'voice') {
            const duration = message.voice_duration || 0;
            const mins = Math.floor(duration / 60);
            const secs = duration % 60;
            const durationStr = `${mins}:${secs.toString().padStart(2, '0')}`;
            messageContent = `
                <div class="chat-voice-player" data-src="${message.file_path}">
                    <button type="button" class="chat-voice-player__btn voice-play-btn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                    </button>
                    <div class="chat-voice-player__progress">
                        <div class="chat-voice-player__progress-bar"></div>
                    </div>
                    <span class="chat-voice-player__duration">${durationStr}</span>
                </div>
            `;
        } else if (type === 'file') {
            const fileSize = formatFileSize(message.file_size);
            messageContent = `
                <div class="chat-file-message">
                    <div class="chat-file-message__icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z"/>
                            <polyline points="14 2 14 8 20 8" fill="none" stroke="currentColor"/>
                        </svg>
                    </div>
                    <div class="chat-file-message__info">
                        <div class="chat-file-message__name">${escapeHtml(message.file_name || message.message)}</div>
                        <div class="chat-file-message__size">${fileSize}</div>
                    </div>
                    <a href="${message.file_path}" download class="chat-file-message__download" title="${translations.downloadFile || 'Download'}">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="7 10 12 15 17 10"/>
                            <line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                    </a>
                </div>
            `;
        } else if (type === 'image') {
            messageContent = `
                <div class="chat-image-message">
                    <img src="${message.file_path}" alt="${escapeHtml(message.file_name || 'Image')}" onclick="window.open('${message.file_path}', '_blank')">
                </div>
            `;
        } else if (type === 'video') {
            messageContent = `
                <div class="chat-video-message" data-src="${message.file_path}">
                    <div class="chat-video-message__thumbnail">
                        <video src="${message.file_path}" preload="metadata" muted></video>
                        <div class="chat-video-message__play-icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="white">
                                <path d="M8 5v14l11-7z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            `;
        } else {
            messageContent = `<div class="chat-message__text">${escapeHtml(message.message)}</div>`;
        }

        // Build action buttons
        let actionsHtml = `<button type="button" class="chat-message__reply-link reply-btn" data-id="${message.id}" data-sender="${message.sender?.full_name || 'User'}" data-text="${escapeHtml((message.message || '').substring(0, 50))}">${translations.reply || 'Reply'}</button>`;

        if (message.can_edit) {
            actionsHtml += ` <button type="button" class="chat-message__reply-link edit-btn" data-id="${message.id}">${translations.edit || 'Edit'}</button>`;
        }

        if (message.can_delete) {
            actionsHtml += ` <button type="button" class="chat-message__reply-link delete-btn chat-message__delete-link" data-id="${message.id}">${translations.delete || 'Delete'}</button>`;
        }

        messageDiv.innerHTML = `
            <div class="chat-message__avatar">
                <img src="${message.sender?.avatar || '/store/default/images/default-avatar.png'}" alt="${message.sender?.full_name || 'User'}">
            </div>
            <div class="chat-message__content">
                <div class="chat-message__header">
                    <span class="chat-message__sender">${message.sender?.full_name || 'User'}</span>
                    <span class="chat-message__time">${message.created_at} ${editedHtml}</span>
                </div>
                <div class="chat-message__bubble">
                    ${replyHtml}
                    ${messageContent}
                </div>
                <div class="chat-message__actions">
                    ${actionsHtml}
                </div>
            </div>
        `;

        return messageDiv;
    }

    function formatFileSize(bytes) {
        if (!bytes) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function scrollToBottom() {
        const messagesDiv = document.querySelector('.chat-room-main-content');
        if (messagesDiv && messagesDiv.SimpleBar) {
            const scrollElement = messagesDiv.SimpleBar.getScrollElement();
            scrollElement.scrollTop = scrollElement.scrollHeight;
        }
    }

    function updateChatClosedState(closed) {
        isChatClosed = closed;
        if (closed) {
            chatClosedNotice.style.display = 'block';
            chatInputWrapper.style.display = 'none';
            if (toggleChatText) toggleChatText.textContent = translations.openChatRoom || 'Open Chat Room';
        } else {
            chatClosedNotice.style.display = 'none';
            chatInputWrapper.style.display = 'flex';
            if (toggleChatText) toggleChatText.textContent = translations.closeChatRoom || 'Close Chat Room';
        }
    }

    async function loadMessages() {
        if (isLoading) return;
        isLoading = true;

        try {
            const response = await fetch(messagesUrl + '?after=' + lastMessageId, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) throw new Error('Failed to load messages');

            const data = await response.json();

            // Update chat closed state
            if (typeof data.is_closed !== 'undefined') {
                updateChatClosedState(data.is_closed);
            }

            // Check if messages were cleared by instructor
            if (data.messages_cleared) {
                chatMessagesContainer.innerHTML = '';
                lastMessageId = 0;
            }

            if (data.messages && data.messages.length > 0) {
                chatEmptyState.style.display = 'none';

                data.messages.forEach(message => {
                    // Check if message already exists (could be an edited version)
                    const existingEl = chatMessagesContainer.querySelector(`[data-id="${message.id}"]`);
                    if (existingEl) {
                        // Replace with updated version
                        const newEl = createMessageElement(message);
                        existingEl.replaceWith(newEl);
                    } else if (message.id > lastMessageId) {
                        const messageEl = createMessageElement(message);
                        chatMessagesContainer.appendChild(messageEl);
                    }
                    if (message.id > lastMessageId) {
                        lastMessageId = message.id;
                    }
                });

                scrollToBottom();
                attachActionListeners();
            } else if (lastMessageId === 0) {
                chatEmptyState.style.display = 'block';
            }

            // Check for deleted messages - remove DOM elements that no longer exist
            // This is handled by the polling: deleted messages won't appear in future responses

            // Update online users
            if (data.online_users) {
                typingUserIds = data.typing_users || [];
                updateOnlineUsers(data.online_users);
            }
        } catch (error) {
            console.error('Error loading messages:', error);
        } finally {
            isLoading = false;
        }
    }

    function updateOnlineUsers(users) {
        if (onlineUsersCount) {
            onlineUsersCount.textContent = '(' + users.length + ')';
        }

        if (!users || users.length === 0) {
            onlineUsersList.innerHTML = `<div class="text-center py-16 text-gray-500 font-12">${translations.noOnlineUsers || 'No online users'}</div>`;
            return;
        }

        let html = '';
        users.forEach(user => {
            const isTyping = typingUserIds.indexOf(user.id) !== -1;
            html += `
                <div class="online-user-item">
                    <div class="online-user-item__avatar">
                        <img src="${user.avatar || '/store/default/images/default-avatar.png'}" alt="${user.full_name}" class="size-40 rounded-circle">
                        <span class="online-user-item__status"></span>
                    </div>
                    <div class="ml-12">
                        <h5 class="font-14 text-dark">${user.full_name}</h5>
                        ${isTyping
                    ? '<span class="chat-typing-indicator"><span class="chat-typing-indicator__dots"><span></span><span></span><span></span></span> ' + (translations.typing || 'typing...') + '</span>'
                    : '<span class="font-12 text-gray-500">' + (user.role || '') + '</span>'
                }
                    </div>
                </div>
            `;
        });
        onlineUsersList.innerHTML = html;
    }

    async function sendMessage() {
        if (isChatClosed) return;

        const message = messageInput.value.trim();
        if (!message) return;

        // Clear input immediately for better UX
        messageInput.value = '';
        const currentReplyToId = replyToId;
        cancelReply();

        sendMessageBtn.disabled = true;

        try {
            const response = await fetch(sendUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    message: message,
                    reply_to_id: currentReplyToId
                })
            });

            if (!response.ok) throw new Error('Failed to send message');

            const data = await response.json();

            if (data.message) {
                chatEmptyState.style.display = 'none';
                const messageEl = createMessageElement(data.message);
                chatMessagesContainer.appendChild(messageEl);
                lastMessageId = data.message.id;
                scrollToBottom();
                attachActionListeners();
            }
        } catch (error) {
            console.error('Error sending message:', error);
            alert(translations.errorSendingMessage || 'Error sending message');
        } finally {
            sendMessageBtn.disabled = false;
        }
    }

    // Edit message
    async function editMessage(messageId, newText) {
        try {
            const response = await fetch(editUrl + messageId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ message: newText })
            });

            if (!response.ok) throw new Error('Failed to edit message');

            const data = await response.json();

            if (data.message) {
                const existingEl = chatMessagesContainer.querySelector(`[data-id="${messageId}"]`);
                if (existingEl) {
                    const newEl = createMessageElement(data.message);
                    existingEl.replaceWith(newEl);
                    attachActionListeners();
                }
            }
        } catch (error) {
            console.error('Error editing message:', error);
        }
    }

    // Delete message
    async function deleteMessage(messageId) {
        if (!confirm(translations.confirmDeleteMessage || 'Are you sure you want to delete this message?')) return;

        try {
            const response = await fetch(deleteUrl + messageId, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) throw new Error('Failed to delete message');

            const el = chatMessagesContainer.querySelector(`[data-id="${messageId}"]`);
            if (el) {
                el.style.transition = 'opacity 0.3s ease';
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 300);
            }

            // Check if container is empty
            setTimeout(() => {
                if (chatMessagesContainer.children.length === 0) {
                    chatEmptyState.style.display = 'block';
                }
            }, 350);
        } catch (error) {
            console.error('Error deleting message:', error);
        }
    }

    // Clear messages (instructor only)
    if (clearMessagesBtn) {
        clearMessagesBtn.addEventListener('click', async function () {
            if (!confirm(translations.confirmClearMessages || 'Are you sure you want to clear all messages?')) return;

            try {
                const response = await fetch(clearUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) throw new Error('Failed to clear messages');

                chatMessagesContainer.innerHTML = '';
                lastMessageId = 0;
                chatEmptyState.style.display = 'block';
                chatToolsDropdown.classList.remove('active');
            } catch (error) {
                console.error('Error clearing messages:', error);
            }
        });
    }

    // Toggle chat (instructor only)
    if (toggleChatBtn) {
        toggleChatBtn.addEventListener('click', async function () {
            try {
                const response = await fetch(toggleUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) throw new Error('Failed to toggle chat');

                const data = await response.json();
                updateChatClosedState(data.is_closed);
                chatToolsDropdown.classList.remove('active');

                // Reload messages to get the system message
                loadMessages();
            } catch (error) {
                console.error('Error toggling chat:', error);
            }
        });
    }

    function setReply(id, sender, text) {
        replyToId = id;
        replyPreviewText.textContent = sender + ': ' + text;
        replyPreview.classList.add('active');
        messageInput.focus();
    }

    function cancelReply() {
        replyToId = null;
        replyPreview.classList.remove('active');
        replyPreviewText.textContent = '';
    }

    function attachActionListeners() {
        // Reply buttons
        document.querySelectorAll('.reply-btn').forEach(btn => {
            btn.removeEventListener('click', handleReplyClick);
            btn.addEventListener('click', handleReplyClick);
        });

        // Edit buttons
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.removeEventListener('click', handleEditClick);
            btn.addEventListener('click', handleEditClick);
        });

        // Delete buttons
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.removeEventListener('click', handleDeleteClick);
            btn.addEventListener('click', handleDeleteClick);
        });

        // Video thumbnails
        document.querySelectorAll('.chat-video-message').forEach(el => {
            el.removeEventListener('click', handleVideoClick);
            el.addEventListener('click', handleVideoClick);
        });
    }

    function handleReplyClick(e) {
        const btn = e.currentTarget;
        setReply(btn.dataset.id, btn.dataset.sender, btn.dataset.text);
    }

    function handleEditClick(e) {
        const btn = e.currentTarget;
        const messageId = btn.dataset.id;
        const messageDiv = chatMessagesContainer.querySelector(`[data-id="${messageId}"]`);
        if (!messageDiv) return;

        const textEl = messageDiv.querySelector('.chat-message__text');
        if (!textEl) return;

        const originalText = textEl.textContent;

        // Replace text with edit input
        const editContainer = document.createElement('div');
        editContainer.className = 'chat-message__edit-container';
        editContainer.innerHTML = `
            <input type="text" class="chat-message__edit-input" value="${escapeHtml(originalText)}" maxlength="2000">
            <div class="chat-message__edit-actions">
                <button type="button" class="chat-message__edit-save">${translations.save || 'Save'}</button>
                <button type="button" class="chat-message__edit-cancel">${translations.cancel || 'Cancel'}</button>
            </div>
        `;

        textEl.replaceWith(editContainer);

        const input = editContainer.querySelector('.chat-message__edit-input');
        input.focus();
        input.setSelectionRange(input.value.length, input.value.length);

        // Save handler
        const saveEdit = () => {
            const newText = input.value.trim();
            if (newText && newText !== originalText) {
                editMessage(messageId, newText);
            } else {
                // Restore original
                const newTextEl = document.createElement('div');
                newTextEl.className = 'chat-message__text';
                newTextEl.textContent = originalText;
                editContainer.replaceWith(newTextEl);
            }
        };

        editContainer.querySelector('.chat-message__edit-save').addEventListener('click', saveEdit);
        editContainer.querySelector('.chat-message__edit-cancel').addEventListener('click', () => {
            const newTextEl = document.createElement('div');
            newTextEl.className = 'chat-message__text';
            newTextEl.textContent = originalText;
            editContainer.replaceWith(newTextEl);
        });

        // Enter to save, Escape to cancel
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                saveEdit();
            } else if (e.key === 'Escape') {
                const newTextEl = document.createElement('div');
                newTextEl.className = 'chat-message__text';
                newTextEl.textContent = originalText;
                editContainer.replaceWith(newTextEl);
            }
        });
    }

    function handleDeleteClick(e) {
        const btn = e.currentTarget;
        deleteMessage(btn.dataset.id);
    }

    function handleVideoClick(e) {
        const videoEl = e.currentTarget;
        const src = videoEl.dataset.src;
        if (!src) return;

        videoModalPlayer.src = src;
        videoModal.style.display = 'flex';
        videoModalPlayer.play();
    }

    // Video modal close handlers
    if (videoModalClose) {
        videoModalClose.addEventListener('click', closeVideoModal);
    }
    if (videoModalCloseBtn) {
        videoModalCloseBtn.addEventListener('click', closeVideoModal);
    }

    function closeVideoModal() {
        videoModalPlayer.pause();
        videoModalPlayer.src = '';
        videoModal.style.display = 'none';
    }

    // Close video modal on Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && videoModal && videoModal.style.display !== 'none') {
            closeVideoModal();
        }
    });

    // Event listeners
    sendMessageBtn.addEventListener('click', sendMessage);

    messageInput.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            sendMessage();
        }
    });

    // Typing detection
    messageInput.addEventListener('input', function () {
        const now = Date.now();
        if (now - lastTypingSent > 2000) {
            lastTypingSent = now;
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            fetch(typingUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken ? csrfToken.content : '',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).catch(function () { });
        }
    });

    cancelReplyBtn.addEventListener('click', cancelReply);

    // File Upload Elements
    const fileInput = document.getElementById('fileInput');
    const attachBtn = document.getElementById('attachBtn');
    const filePreview = document.getElementById('filePreview');
    const filePreviewName = document.getElementById('filePreviewName');
    const filePreviewSize = document.getElementById('filePreviewSize');
    const removeFileBtn = document.getElementById('removeFile');

    // Voice Recording Elements
    const voiceBtn = document.getElementById('voiceBtn');
    const voiceRecording = document.getElementById('voiceRecording');
    const recordingTime = document.getElementById('recordingTime');
    const cancelRecordingBtn = document.getElementById('cancelRecording');
    const sendRecordingBtn = document.getElementById('sendRecording');

    let selectedFile = null;
    let mediaRecorder = null;
    let audioChunks = [];
    let recordingStartTime = null;
    let recordingTimer = null;

    // File Attachment
    attachBtn.addEventListener('click', () => fileInput.click());

    fileInput.addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (!file) return;

        // Check file size based on type
        const isImage = file.type.startsWith('image/');
        const isVideo = file.type.startsWith('video/');
        let maxSize;
        let maxLabel;

        if (isVideo) {
            maxSize = 100 * 1024 * 1024; // 100MB
            maxLabel = '100';
        } else if (isImage) {
            maxSize = 10 * 1024 * 1024; // 10MB
            maxLabel = '10';
        } else {
            maxSize = 50 * 1024 * 1024; // 50MB
            maxLabel = '50';
        }

        if (file.size > maxSize) {
            const msg = isVideo
                ? (translations.videoTooLarge || 'Video too large. Max size: :size MB').replace(':size', maxLabel)
                : (translations.fileTooLarge || 'File too large. Max size: :size MB').replace(':size', maxLabel);
            alert(msg);
            fileInput.value = '';
            return;
        }

        selectedFile = file;
        filePreviewName.textContent = file.name;
        filePreviewSize.textContent = formatFileSize(file.size);
        filePreview.style.display = 'flex';
    });

    removeFileBtn.addEventListener('click', function () {
        selectedFile = null;
        fileInput.value = '';
        filePreview.style.display = 'none';
    });

    // Voice Recording
    voiceBtn.addEventListener('click', async function () {
        if (isChatClosed) return;

        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            mediaRecorder = new MediaRecorder(stream);
            audioChunks = [];

            mediaRecorder.ondataavailable = (e) => audioChunks.push(e.data);

            mediaRecorder.onstop = () => {
                stream.getTracks().forEach(track => track.stop());
            };

            mediaRecorder.start();
            recordingStartTime = Date.now();

            voiceRecording.style.display = 'inline-flex';

            recordingTimer = setInterval(() => {
                const elapsed = Math.floor((Date.now() - recordingStartTime) / 1000);
                const mins = Math.floor(elapsed / 60);
                const secs = elapsed % 60;
                recordingTime.textContent = `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
            }, 1000);

        } catch (err) {
            console.error('Microphone access denied:', err);
            alert(translations.microphoneAccessDenied || 'Microphone access denied');
        }
    });

    cancelRecordingBtn.addEventListener('click', function () {
        if (mediaRecorder && mediaRecorder.state !== 'inactive') {
            mediaRecorder.stop();
        }
        clearInterval(recordingTimer);
        voiceRecording.style.display = 'none';
        audioChunks = [];
    });

    sendRecordingBtn.addEventListener('click', async function () {
        if (!mediaRecorder || mediaRecorder.state === 'inactive') return;

        const duration = Math.floor((Date.now() - recordingStartTime) / 1000);
        mediaRecorder.stop();
        clearInterval(recordingTimer);

        voiceRecording.style.display = 'none';

        // Wait for data to be available
        await new Promise(resolve => setTimeout(resolve, 100));

        const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
        const formData = new FormData();
        formData.append('type', 'voice');
        formData.append('file', audioBlob, 'voice.webm');
        formData.append('duration', duration);
        if (replyToId) formData.append('reply_to_id', replyToId);

        cancelReply();

        try {
            const response = await fetch(sendUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: formData
            });

            if (!response.ok) throw new Error('Failed to send voice');

            const data = await response.json();
            if (data.message) {
                chatEmptyState.style.display = 'none';
                chatMessagesContainer.appendChild(createMessageElement(data.message));
                lastMessageId = data.message.id;
                scrollToBottom();
                attachActionListeners();
            }
        } catch (error) {
            console.error('Error sending voice:', error);
        }

        audioChunks = [];
    });

    // Override send button to handle files
    sendMessageBtn.removeEventListener('click', sendMessage);
    sendMessageBtn.addEventListener('click', async function () {
        if (isChatClosed) return;

        if (selectedFile) {
            // Determine file type
            const isImage = selectedFile.type.startsWith('image/');
            const isVideo = selectedFile.type.startsWith('video/');
            let fileType = 'file';
            if (isImage) fileType = 'image';
            else if (isVideo) fileType = 'video';

            const formData = new FormData();
            formData.append('type', fileType);
            formData.append('file', selectedFile);
            if (replyToId) formData.append('reply_to_id', replyToId);

            selectedFile = null;
            fileInput.value = '';
            filePreview.style.display = 'none';
            cancelReply();
            sendMessageBtn.disabled = true;

            try {
                const response = await fetch(sendUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: formData
                });

                if (!response.ok) throw new Error('Failed to send file');

                const data = await response.json();
                if (data.message) {
                    chatEmptyState.style.display = 'none';
                    chatMessagesContainer.appendChild(createMessageElement(data.message));
                    lastMessageId = data.message.id;
                    scrollToBottom();
                    attachActionListeners();
                }
            } catch (error) {
                console.error('Error sending file:', error);
            } finally {
                sendMessageBtn.disabled = false;
            }
        } else {
            // Send text message
            sendMessage();
        }
    });

    // Voice Player - handle playback
    let currentAudio = null;
    let currentPlayBtn = null;

    chatMessagesContainer.addEventListener('click', function (e) {
        const playBtn = e.target.closest('.voice-play-btn');
        if (!playBtn) return;

        const player = playBtn.closest('.chat-voice-player');
        const audioSrc = player.dataset.src;
        const progressBar = player.querySelector('.chat-voice-player__progress-bar');
        const durationSpan = player.querySelector('.chat-voice-player__duration');

        // If clicking the same button, toggle play/pause
        if (currentAudio && currentPlayBtn === playBtn) {
            if (currentAudio.paused) {
                currentAudio.play();
                playBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>';
            } else {
                currentAudio.pause();
                playBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>';
            }
            return;
        }

        // Stop previous audio
        if (currentAudio) {
            currentAudio.pause();
            currentAudio = null;
            if (currentPlayBtn) {
                currentPlayBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>';
            }
        }

        // Create new audio
        currentAudio = new Audio(audioSrc);
        currentPlayBtn = playBtn;

        currentAudio.addEventListener('loadedmetadata', function () {
            const mins = Math.floor(currentAudio.duration / 60);
            const secs = Math.floor(currentAudio.duration % 60);
            durationSpan.textContent = `${mins}:${secs.toString().padStart(2, '0')}`;
        });

        currentAudio.addEventListener('timeupdate', function () {
            const progress = (currentAudio.currentTime / currentAudio.duration) * 100;
            progressBar.style.width = progress + '%';
        });

        currentAudio.addEventListener('ended', function () {
            playBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>';
            progressBar.style.width = '0%';
            currentAudio = null;
            currentPlayBtn = null;
        });

        currentAudio.play();
        playBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>';
    });

    // Initial load
    loadMessages();

    // Poll for new messages every 1000ms (near real-time)
    setInterval(loadMessages, 1000);
})();
