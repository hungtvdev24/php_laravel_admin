@extends('layouts.admin')

@section('title', 'Chat với ' . $user->name)

@section('content')
    <div class="container-fluid">
        <div class="chat-header">
            <div class="d-flex align-items-center">
                <a href="{{ route('admin.chat.index') }}" class="btn btn-light me-3">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div class="user-info">
                    <h3 class="mb-0">Chat với {{ $user->name }}</h3>
                    <small>Trò chuyện với {{ $user->email }}</small>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="chat-container card">
            <div class="chat-box" id="chat-box">
                @foreach($messages as $message)
                    <div class="message {{ $message->sender_id == $senderId && $message->sender_type == 'App\\Models\\Admin' ? 'sent' : 'received' }}" data-message-id="{{ $message->id }}">
                        @if($message->sender_id != $senderId || $message->sender_type != 'App\\Models\\Admin')
                            <div class="avatar">
                                <img src="https://via.placeholder.com/40" alt="Avatar">
                            </div>
                        @endif
                        <div class="message-content">
                            <div class="message-text">
                                {{ $message->content }}
                            </div>
                            <small class="message-time">{{ $message->created_at->format('H:i d/m/Y') }}</small>
                        </div>
                    </div>
                @endforeach
            </div>

            <form action="{{ route('admin.chat.send', $user->id) }}" method="POST" class="chat-form" id="chat-form">
                @csrf
                <div class="input-group">
                    <textarea name="content" class="form-control" rows="1" placeholder="Nhập tin nhắn..." required></textarea>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.0/dist/echo.iife.js"></script>
    <script>
        window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        let token = document.head.querySelector('meta[name="csrf-token"]');
        if (token) {
            window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
        } else {
            console.error('[Chat] Không tìm thấy CSRF token');
        }

        window.Pusher = Pusher;
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: '{{ env('PUSHER_APP_KEY') }}',
            cluster: '{{ env('PUSHER_APP_CLUSTER') }}',
            forceTLS: true
        });

        // Lấy thông tin người dùng hiện tại
        const currentUserId = {{ $senderId ?? 'null' }};
        const currentUserType = 'App\\Models\\Admin';
        console.log('[Chat] Current user:', { id: currentUserId, type: currentUserType });

        // Hàm cuộn xuống cuối khung chat
        function scrollToBottom() {
            const chatBox = document.getElementById('chat-box');
            setTimeout(() => {
                chatBox.scrollTo({ top: chatBox.scrollHeight, behavior: 'smooth' });
                console.log('[Chat] Đã cuộn xuống tin nhắn mới nhất');
            }, 50);
        }

        // Hàm định dạng thời gian theo múi giờ Hà Nội
        function formatHanoiTime(dateString) {
            const date = new Date(dateString);
            return date.toLocaleString('vi-VN', {
                timeZone: 'Asia/Ho_Chi_Minh',
                hour: '2-digit',
                minute: '2-digit',
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
        }

        // Hàm thêm tin nhắn
        function appendMessage(content, isSent, createdAt, messageId = null) {
            const chatBox = document.getElementById('chat-box');
            // Kiểm tra trùng lặp tin nhắn
            if (messageId && document.querySelector(`[data-message-id="${messageId}"]`)) {
                console.log('[Chat] Tin nhắn đã tồn tại, bỏ qua:', messageId);
                return;
            }

            const messageDiv = document.createElement('div');
            messageDiv.classList.add('message', isSent ? 'sent' : 'received');
            if (messageId) {
                messageDiv.setAttribute('data-message-id', messageId);
            }

            let messageContent = `
                ${!isSent ? '<div class="avatar"><img src="https://via.placeholder.com/40" alt="Avatar"></div>' : ''}
                <div class="message-content">
                    <div class="message-text">${content}</div>
                    <small class="message-time">${formatHanoiTime(createdAt)}</small>
                </div>
            `;
            messageDiv.innerHTML = messageContent;
            chatBox.appendChild(messageDiv);
            scrollToBottom();
            console.log('[Chat] Đã thêm tin nhắn:', { content, isSent, time: formatHanoiTime(createdAt), messageId });
        }

        // Lắng nghe tin nhắn mới qua Pusher
        window.Echo.channel('chat.{{ $user->id }}')
            .listen('.message.sent', (e) => {
                const isSent = e.message.sender_id == currentUserId && e.message.sender_type == currentUserType;
                appendMessage(e.message.content, isSent, e.message.created_at, e.message.id);
                console.log('[Chat] Nhận tin nhắn thời gian thực qua Pusher:', e.message);
            });

        // Xử lý nhấn Enter hai lần và gửi form
        document.addEventListener('DOMContentLoaded', function() {
            const chatBox = document.getElementById('chat-box');
            const textarea = document.querySelector('.chat-form textarea');
            const form = document.querySelector('.chat-form');
            let lastEnterTime = 0;

            // Tự động cuộn khi tải trang
            scrollToBottom();
            console.log('[Chat] Khởi tạo khung chat, cuộn xuống đầu tiên');

            // Sự kiện nhấn phím
            textarea.addEventListener('keydown', function(event) {
                if (event.key === 'Enter' && !event.shiftKey) {
                    event.preventDefault();
                    const currentTime = new Date().getTime();
                    console.log('[Chat] Nhấn Enter, khoảng cách thời gian:', currentTime - lastEnterTime);
                    if (currentTime - lastEnterTime < 500) {
                        const content = textarea.value.trim();
                        if (content) {
                            console.log('[Chat] Chuẩn bị gửi tin nhắn:', content);
                            textarea.value = '';
                            // Gửi AJAX và chờ Pusher
                            axios.post(form.action, {
                                content: content,
                                _token: token.content
                            })
                            .then(response => {
                                console.log('[Chat] Tin nhắn gửi thành công qua AJAX:', response.data);
                            })
                            .catch(error => {
                                console.error('[Chat] Lỗi khi gửi tin nhắn:', error);
                                alert('Không thể gửi tin nhắn. Vui lòng thử lại.');
                            });
                        } else {
                            console.log('[Chat] Tin nhắn rỗng, không gửi');
                        }
                    }
                    lastEnterTime = currentTime;
                }
            });

            // Xử lý gửi bằng nút
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                const content = textarea.value.trim();
                if (content) {
                    console.log('[Chat] Gửi tin nhắn qua nút:', content);
                    textarea.value = '';
                    axios.post(form.action, {
                        content: content,
                        _token: token.content
                    })
                    .then(response => {
                        console.log('[Chat] Tin nhắn gửi thành công qua AJAX:', response.data);
                    })
                    .catch(error => {
                        console.error('[Chat] Lỗi khi gửi tin nhắn:', error);
                        alert('Không thể gửi tin nhắn. Vui lòng thử lại.');
                    });
                } else {
                    console.log('[Chat] Tin nhắn rỗng, không gửi');
                }
            });
        });
    </script>

    <style>
        .chat-header {
            padding: 15px 0;
            border-bottom: 1px solid #e0e0e0;
            margin-bottom: 20px;
        }

        .chat-header h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
        }

        .chat-header small {
            color: #666;
        }

        .chat-container {
            border: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        .chat-box {
            height: 500px;
            overflow-y: auto;
            padding: 20px;
            background: #f0f2f5;
            border-bottom: 1px solid #e0e0e0;
        }

        .message {
            display: flex;
            margin-bottom: 15px;
            align-items: flex-start;
        }

        .message.sent {
            justify-content: flex-end;
        }

        .message.received {
            justify-content: flex-start;
        }

        .message .avatar {
            width: 40px;
            height: 40px;
            margin-right: 10px;
        }

        .message .avatar img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }

        .message-content {
            max-width: 60%;
        }

        .message-text {
            padding: 10px 15px;
            border-radius: 18px;
            font-size: 1rem;
            line-height: 1.4;
            word-wrap: break-word;
        }

        .message.sent .message-text {
            background-color: #0084ff;
            color: white;
            border-bottom-right-radius: 4px;
        }

        .message.received .message-text {
            background-color: #ffffff;
            color: #333;
            border: 1px solid #e0e0e0;
            border-bottom-left-radius: 4px;
        }

        .message-time {
            font-size: 0.75rem;
            color: #999;
            margin-top: 5px;
            display: block;
            text-align: right;
        }

        .message.received .message-time {
            text-align: left;
        }

        .chat-form {
            padding: 15px;
            background: #fff;
        }

        .chat-form .input-group {
            display: flex;
            align-items: center;
        }

        .chat-form textarea {
            resize: none;
            border-radius: 20px;
            padding: 10px 15px;
            font-size: 1rem;
            border: 1px solid #e0e0e0;
            box-shadow: none;
            flex: 1;
        }

        .chat-form textarea:focus {
            border-color: #0084ff;
            box-shadow: 0 0 5px rgba(0, 132, 255, 0.3);
            outline: none;
        }

        .chat-form .btn-primary {
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 10px;
            background-color: #0084ff;
            border: none;
        }

        .chat-form .btn-primary:hover {
            background-color: #006cdc;
        }

        .chat-form .btn-primary i {
            font-size: 1rem;
        }
    </style>
@endsection