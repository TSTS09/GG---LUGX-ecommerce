// setpassword.js
$(document).ready(function() {
    // Password visibility toggle
    $('.set-password-toggle').click(function() {
        const input = $($(this).attr('toggle'));
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            $(this).removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            $(this).removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Form submission
    $('#setPasswordForm').on('submit', function(e) {
        e.preventDefault();

        const newPassword = $('#new_password').val().trim();
        const confirmPassword = $('#confirm_password').val().trim();

        // Basic validation
        if (!newPassword || !confirmPassword) {
            Swal.fire('Error', 'Both password fields are required.', 'error');
            return;
        }

        if (newPassword.length < 6) {
            Swal.fire('Error', 'Password must be at least 6 characters long.', 'error');
            return;
        }

        if (newPassword !== confirmPassword) {
            Swal.fire('Error', 'Passwords do not match.', 'error');
            return;
        }

        $.ajax({
            url: '../Actions/set_password_action.php',
            type: 'POST',
            data: {
                customer_email: $('#customer_email').val(),
                new_password: newPassword,
                confirm_password: confirmPassword
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: 'Success',
                        text: response.message,
                        icon: 'success',
                        showConfirmButton: true
                    }).then(() => {
                        window.location.href = '../login/login.php';
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: response.message,
                        icon: 'error'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    title: 'Error',
                    text: 'An error occurred. Please try again.',
                    icon: 'error'
                });
            }
        });
    });
});