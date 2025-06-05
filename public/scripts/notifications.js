document.addEventListener('DOMContentLoaded', () => {
    fetch('/api/notifications')
        .then(response => response.json())
        .then(data => renderNotifications(data))
        .catch(err => console.error('Notification fetch error:', err));
});

function renderNotifications(notifications) {
    const container = document.getElementById('notification-list');
    container.innerHTML = '';

    if (notifications.length === 0) {
        container.innerHTML = '<p>No notifications available.</p>';
        return;
    }

    notifications.forEach(notification => {
        const wrapper = document.createElement('div');
        wrapper.className = 'inside-grid-container notification-item';
        wrapper.style.cursor = 'pointer';
        wrapper.dataset.id = notification.id;

        const icon = document.createElement('img');
        icon.src = notification.is_read ? '/public/images/read_mail.png' : '/public/images/unread_mail.png';
        icon.className = notification.is_read ? 'seen' : 'unseen';

        const message = document.createElement('div');
        message.className = 'message';
        message.innerText = `${notification.created_at} ${notification.message}`;

        wrapper.appendChild(icon);
        wrapper.appendChild(message);
        container.appendChild(wrapper);

        wrapper.addEventListener('click', () => {
            if (!notification.is_read) {
                markNotificationAsRead(notification.id, wrapper, icon);
            }
        });
    });
}

function markNotificationAsRead(id, wrapper, iconElement) {
    fetch('/markAsRead', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id })
    })
    .then(res => res.json())
    .then(response => {
        if (response.success) {
            iconElement.src = '/public/images/read_mail.png';
            iconElement.className = 'seen';
        }
    })
    .catch(err => console.error('Error marking as read:', err));
}
