/**
 * 邮箱绑定管理JavaScript函数
 */

// Dcat Admin对象
if (typeof Dcat === 'undefined') {
    var Dcat = {};
}

/**
 * 发送验证邮件
 * @param {number} id 邮箱绑定ID
 */
function sendVerification(id) {
    if (!confirm('确定要发送验证邮件吗？')) {
        return;
    }

    $.ajax({
        url: '/admin/module_account/email-bindings/' + id + '/send-verification',
        method: 'POST',
        dataType: 'json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            if (response.status) {
                alert(response.message);
                location.reload();
            } else {
                alert(response.error || '发送验证邮件失败');
            }
        },
        error: function (xhr) {
            var message = '发送验证邮件失败';
            if (xhr.responseJSON && xhr.responseJSON.error) {
                message = xhr.responseJSON.error;
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            alert(message);
        }
    });
}

/**
 * 设为主邮箱
 * @param {number} id 邮箱绑定ID
 */
function setPrimaryEmail(id) {
    if (!confirm('确定要设为主邮箱吗？\n这将替换当前的主邮箱设置。')) {
        return;
    }

    $.ajax({
        url: '/admin/module_account/email-bindings/' + id + '/set-primary',
        method: 'POST',
        dataType: 'json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            if (response.status) {
                alert(response.message);
                location.reload();
            } else {
                alert(response.error || '设置主邮箱失败');
            }
        },
        error: function (xhr) {
            var message = '设置主邮箱失败';
            if (xhr.responseJSON && xhr.responseJSON.error) {
                message = xhr.responseJSON.error;
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            alert(message);
        }
    });
}

/**
 * 删除邮箱绑定
 * @param {number} id 邮箱绑定ID
 */
function deleteEmailBinding(id) {
    if (!confirm('确定要删除邮箱绑定吗？\n注意：主邮箱不能删除！')) {
        return;
    }

    $.ajax({
        url: '/admin/module_account/email-bindings/' + id + '/delete',
        method: 'DELETE',
        dataType: 'json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            if (response.status) {
                alert(response.message);
                location.reload();
            } else {
                alert(response.error || '删除邮箱绑定失败');
            }
        },
        error: function (xhr) {
            var message = '删除邮箱绑定失败';
            if (xhr.responseJSON && xhr.responseJSON.error) {
                message = xhr.responseJSON.error;
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            alert(message);
        }
    });
}

/**
 * CSRF Token设置（如果需要）
 */
function setupCsrfToken() {
    var token = document.querySelector('meta[name="csrf-token"]');
    if (token) {
        Dcat.requestSetup({
            headers: {
                'X-CSRF-TOKEN': token.getAttribute('content')
            }
        });
    }
}

// 页面加载完成后设置CSRF Token
$(document).ready(function() {
    setupCsrfToken();
});