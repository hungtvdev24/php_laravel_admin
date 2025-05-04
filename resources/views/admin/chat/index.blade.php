@extends('layouts.admin')

@section('title', 'Chat - Danh sách người dùng')

@section('content')
    <h1>Danh sách người dùng để chat</h1>
    <p>Chọn một người dùng để bắt đầu trò chuyện</p>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <table class="table table-bordered" id="user-table">
                <thead class="table-warm-header">
                    <tr>
                        <th>Tên</th>
                        <th>Email</th>
                        <th>Số điện thoại</th>
                        <th>Tin nhắn gần đây</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody id="user-list">
                    @forelse($users as $user)
                        <tr data-user-id="{{ $user->id }}">
                            <td>
                                @if(isset($unreadMessages[$user->id]) && $unreadMessages[$user->id] > 0)
                                    <span class="badge badge-danger unread-badge" style="background-color: #dc3545; color: white; margin-bottom: 5px; display: inline-block;">
                                        Chưa đọc (<span class="unread-count">{{ $unreadMessages[$user->id] }}</span>)
                                    </span><br>
                                @endif
                                {{ $user->name }}
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->phone ?? 'N/A' }}</td>
                            <td class="latest-message">
                                @if(isset($latestMessages[$user->id]) && $latestMessages[$user->id])
                                    <small class="message-time">{{ $latestMessages[$user->id] }}</small><br>
                                    <span class="message-content">{{ Str::limit($latestMessageContents[$user->id] ?? '', 50) }}</span>
                                @else
                                    <span>Chưa có tin nhắn</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.chat.show', $user->id) }}" class="btn btn-warm-cool btn-sm">
                                    <i class="fas fa-comment"></i> Chat
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Không có người dùng nào để hiển thị.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
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
            console.log('[ChatIndex] CSRF token đã được thiết lập:', token.content);
        } else {
            console.error('[ChatIndex] Không tìm thấy CSRF token');
        }

        // Khởi tạo Pusher và Echo
        window.Pusher = Pusher;
        console.log('[ChatIndex] Khởi tạo Pusher với key:', '{{ env('PUSHER_APP_KEY') }}', 'và cluster:', '{{ env('PUSHER_APP_CLUSTER') }}');
        try {
            window.Echo = new Echo({
                broadcaster: 'pusher',
                key: '{{ env('PUSHER_APP_KEY') }}',
                cluster: '{{ env('PUSHER_APP_CLUSTER') }}',
                forceTLS: true
            });
            console.log('[ChatIndex] Khởi tạo Laravel Echo thành công');
        } catch (error) {
            console.error('[ChatIndex] Lỗi khi khởi tạo Laravel Echo:', error);
        }

        // Lấy thông tin admin hiện tại
        const adminId = {{ $admin->id ?? 'null' }};
        const adminType = 'App\\Models\\Admin';
        if (!adminId) {
            console.error('[ChatIndex] Không tìm thấy adminId. Vui lòng kiểm tra lại phiên đăng nhập.');
        } else {
            console.log('[ChatIndex] Current admin:', { id: adminId, type: adminType });
        }

        // Danh sách người dùng ban đầu
        const initialUsers = @json($initialUsers);
        console.log('[ChatIndex] Danh sách người dùng ban đầu:', initialUsers);

        // Hàm định dạng thời gian theo múi giờ Hà Nội
        function formatHanoiTime(dateString) {
            try {
                const date = new Date(dateString);
                return date.toLocaleString('vi-VN', {
                    timeZone: 'Asia/Ho_Chi_Minh',
                    hour: '2-digit',
                    minute: '2-digit',
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric'
                });
            } catch (error) {
                console.error('[ChatIndex] Lỗi khi định dạng thời gian:', error, 'Date string:', dateString);
                return 'N/A';
            }
        }

        // Hàm cập nhật hoặc thêm người dùng vào danh sách, luôn đưa lên đầu
        function updateUserList(userId, unreadCount, latestMessageTime, latestMessageContent) {
            console.log('[ChatIndex] Cập nhật giao diện cho user:', { userId, unreadCount, latestMessageTime, latestMessageContent });

            const userList = document.getElementById('user-list');
            if (!userList) {
                console.error('[ChatIndex] Không tìm thấy user-list element');
                return;
            }

            let userRow = document.querySelector(`tr[data-user-id="${userId}"]`);

            // Nếu người dùng chưa có trong danh sách, tạo mới
            if (!userRow) {
                const user = initialUsers.find(u => u.id == userId);
                if (!user) {
                    console.log('[ChatIndex] Không tìm thấy người dùng trong initialUsers:', userId);
                    return;
                }
                userRow = document.createElement('tr');
                userRow.setAttribute('data-user-id', userId);
                userRow.innerHTML = `
                    <td>
                        ${unreadCount > 0 ? `<span class="badge badge-danger unread-badge" style="background-color: #dc3545; color: white; margin-bottom: 5px; display: inline-block;">
                            Chưa đọc (<span class="unread-count">${unreadCount}</span>)
                        </span><br>` : ''}
                        ${user.name}
                    </td>
                    <td>${user.email}</td>
                    <td>${user.phone || 'N/A'}</td>
                    <td class="latest-message">
                        ${latestMessageTime ? `
                            <small class="message-time">${latestMessageTime}</small><br>
                            <span class="message-content">${latestMessageContent ? (latestMessageContent.length > 50 ? latestMessageContent.substring(0, 50) + '...' : latestMessageContent) : ''}</span>
                        ` : '<span>Chưa có tin nhắn</span>'}
                    </td>
                    <td>
                        <a href="/admin/chat/${userId}" class="btn btn-warm-cool btn-sm">
                            <i class="fas fa-comment"></i> Chat
                        </a>
                    </td>
                `;
                userList.insertAdjacentElement('afterbegin', userRow); // Thêm vào đầu danh sách
                console.log('[ChatIndex] Đã thêm user mới vào đầu danh sách:', userId);
            } else {
                // Cập nhật số tin nhắn chưa đọc
                const badge = userRow.querySelector('.unread-badge');
                if (unreadCount > 0) {
                    if (badge) {
                        badge.querySelector('.unread-count').textContent = unreadCount;
                        console.log('[ChatIndex] Cập nhật số tin nhắn chưa đọc:', unreadCount);
                    } else {
                        const nameCell = userRow.querySelector('td:first-child');
                        nameCell.insertAdjacentHTML('afterbegin', `
                            <span class="badge badge-danger unread-badge" style="background-color: #dc3545; color: white; margin-bottom: 5px; display: inline-block;">
                                Chưa đọc (<span class="unread-count">${unreadCount}</span>)
                            </span><br>
                        `);
                        console.log('[ChatIndex] Thêm badge tin nhắn chưa đọc:', unreadCount);
                    }
                } else if (badge) {
                    badge.remove();
                    console.log('[ChatIndex] Xóa badge tin nhắn chưa đọc');
                }

                // Cập nhật tin nhắn gần đây
                const latestMessageCell = userRow.querySelector('.latest-message');
                latestMessageCell.innerHTML = latestMessageTime ? `
                    <small class="message-time">${latestMessageTime}</small><br>
                    <span class="message-content">${latestMessageContent ? (latestMessageContent.length > 50 ? latestMessageContent.substring(0, 50) + '...' : latestMessageContent) : ''}</span>
                ` : '<span>Chưa có tin nhắn</span>';
                console.log('[ChatIndex] Cập nhật tin nhắn gần đây:', { latestMessageTime, latestMessageContent });

                // Đưa hàng lên đầu danh sách
                userList.insertAdjacentElement('afterbegin', userRow);
                console.log('[ChatIndex] Đưa user lên đầu danh sách:', userId);
            }
        }

        // Hàm xử lý cập nhật danh sách người dùng từ tin nhắn nhận được
        function handleMessageUpdate(userId) {
            console.log('[ChatIndex] Gọi API để lấy tin nhắn cho user:', userId);
            axios.get(`/api/admin/messages/${userId}`, {
                params: { receiver_type: 'App\\Models\\Admin' }
            })
            .then(response => {
                console.log('[ChatIndex] Nhận dữ liệu từ API:', response.data);
                const messages = response.data.data;
                if (!messages || messages.length === 0) {
                    console.log('[ChatIndex] Không có tin nhắn nào được trả về từ API cho user:', userId);
                    return;
                }

                const unreadCount = messages.filter(msg => 
                    msg.sender_id == userId && 
                    msg.sender_type === 'App\\Models\\User' && 
                    msg.receiver_id == adminId && 
                    msg.receiver_type === 'App\\Models\\Admin' && 
                    !msg.is_read
                ).length;

                const latestMessage = messages.sort((a, b) => new Date(b.created_at) - new Date(a.created_at))[0];
                const latestMessageTime = latestMessage ? formatHanoiTime(latestMessage.created_at) : null;
                const latestMessageContent = latestMessage ? latestMessage.content : null;

                console.log('[ChatIndex] Cập nhật danh sách:', { userId, unreadCount, latestMessageTime, latestMessageContent });
                updateUserList(userId, unreadCount, latestMessageTime, latestMessageContent);
            })
            .catch(error => {
                console.error('[ChatIndex] Lỗi khi gọi API lấy tin nhắn:', error);
                if (error.response) {
                    console.error('[ChatIndex] Chi tiết lỗi API:', {
                        status: error.response.status,
                        data: error.response.data
                    });
                }
                alert('Không thể tải tin nhắn mới. Vui lòng thử lại sau.');
            });
        }

        // Lắng nghe tất cả các kênh chat của người dùng để cập nhật thời gian thực
        initialUsers.forEach(user => {
            console.log('[ChatIndex] Đăng ký lắng nghe kênh:', `chat.${user.id}`);
            window.Echo.channel(`chat.${user.id}`)
                .listen('.message.sent', (e) => {
                    console.log('[ChatIndex] Nhận tin nhắn thời gian thực từ kênh chat.' + user.id + ':', e.message);

                    const isRelevantMessage =
                        (e.message.sender_type === 'App\\Models\\User' && e.message.sender_id == user.id && e.message.receiver_id == adminId && e.message.receiver_type == adminType) ||
                        (e.message.sender_id == adminId && e.message.sender_type == adminType && e.message.receiver_id == user.id && e.message.receiver_type == 'App\\Models\\User');

                    if (isRelevantMessage) {
                        console.log('[ChatIndex] Tin nhắn liên quan từ kênh user, cập nhật danh sách:', e.message);
                        handleMessageUpdate(user.id);
                    } else {
                        console.log('[ChatIndex] Tin nhắn không liên quan từ kênh user:', e.message);
                    }
                })
                .error(error => {
                    console.error('[ChatIndex] Lỗi khi lắng nghe kênh chat.' + user.id + ':', error);
                });
        });

        // Lắng nghe kênh của admin để nhận tin nhắn từ người dùng
        console.log('[ChatIndex] Đăng ký lắng nghe kênh của admin:', `chat.${adminId}`);
        window.Echo.channel(`chat.${adminId}`)
            .listen('.message.sent', (e) => {
                console.log('[ChatIndex] Nhận tin nhắn thời gian thực từ kênh chat.' + adminId + ':', e.message);

                let userId = null;
                if (e.message.sender_type === 'App\\Models\\User' && e.message.receiver_id == adminId && e.message.receiver_type == adminType) {
                    userId = e.message.sender_id; // Người dùng gửi tin nhắn cho admin
                } else if (e.message.sender_id == adminId && e.message.sender_type == adminType && e.message.receiver_type == 'App\\Models\\User') {
                    userId = e.message.receiver_id; // Admin gửi tin nhắn cho người dùng
                }

                if (userId) {
                    console.log('[ChatIndex] Tin nhắn liên quan với userId từ kênh admin:', userId);
                    handleMessageUpdate(userId);
                } else {
                    console.log('[ChatIndex] Tin nhắn không liên quan từ kênh admin:', e.message);
                }
            })
            .error(error => {
                console.error('[ChatIndex] Lỗi khi lắng nghe kênh chat.' + adminId + ':', error);
            });
    </script>

    <style>
        .message-time {
            color: #999;
            font-size: 0.85rem;
        }
        .message-content {
            color: #333;
            font-size: 0.9rem;
        }
    </style>
@endsection