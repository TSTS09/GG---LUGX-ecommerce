$(document).ready(function() {
    // Check for timeout parameter in URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('timeout') && urlParams.get('timeout') == '1') {
        // Display session timeout message
        Swal.fire({
            icon: "warning",
            title: "Session Expired",
            text: "Your session has expired due to inactivity. Please sign in again."
        });
    }
    
    $('.toggle-password').click(function() {
        var input = $($(this).attr('toggle'));
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            $(this).removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            $(this).removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });
    $('#loginForm').on('submit', function(e) {
        e.preventDefault(); // Prevent the default form submission
        // Validate form
        if (!validateForm()) {
            return;
        }
        // Show loading indicator
        Swal.fire({
            title: 'Signing in...',
            text: 'Please wait',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        // Perform the AJAX request
        $.ajax({
            url: '../Login/login_process.php',
            type: 'POST',
            data: {
                customer_email: $('#customer_email').val(),
                customer_pass: $('#customer_pass').val()
            },
            dataType: 'json',
            success: function(response) {
                Swal.close();
                if (response.success) {
                    Swal.fire({
                        icon: "success",
                        title: "Success",
                        text: response.message
                    }).then(() => {
                        window.location.href = response.redirect;
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Login Failed",
                        text: response.message || "Invalid email or password"
                    });
                }
            },
            error: function(xhr, status, error) {
                Swal.close();
                console.log("AJAX Error:");
                console.log("Status: " + status);
                console.log("Error: " + error);
                console.log("Response Text: ", xhr.responseText);
               
                let errorMessage = 'An error occurred. Please try again.';
               
                try {
                    // Try to parse response as JSON
                    let jsonResponse = JSON.parse(xhr.responseText);
                    if (jsonResponse && jsonResponse.message) {
                        errorMessage = jsonResponse.message;
                    }
                } catch (e) {
                    // If not valid JSON, use the raw response or error
                    if (xhr.responseText) {
                        errorMessage = 'Server error: ' + xhr.responseText.substring(0, 100) +
                                      (xhr.responseText.length > 100 ? '...' : '');
                    }
                }
               
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage
                });
            }
        });
    });
});
function validateForm() {
    var email = $('#customer_email').val().trim();
    var password = $('#customer_pass').val().trim();
   
    // Validate email
    if (!validateEmail(email)) {
        Swal.fire({
            icon: 'error',
            title: 'Invalid Email',
            text: 'Please enter a valid email address'
        });
        return false;
    }
   
    // Validate password is not empty
    if (!password) {
        Swal.fire({
            icon: 'error',
            title: 'Password Required',
            text: 'Please enter your password'
        });
        return false;
    }
   
    return true;
}
function validateEmail(email) {
    var re = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
    return re.test(email);
}