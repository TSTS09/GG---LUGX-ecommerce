$(document).ready(function() {
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

    $('#registrationForm').on('submit', function(e) {
        e.preventDefault(); // Prevent the default form submission

        // Validate form
        if (!validateForm()) {
            return;
        }

        // Create FormData object for handling file uploads
        var formData = new FormData();
        formData.append('customer_name', $('#customer_name').val());
        formData.append('customer_email', $('#customer_email').val());
        formData.append('customer_country', $('#customer_country').val());
        formData.append('customer_city', $('#customer_city').val());
        formData.append('customer_contact', $('#customer_contact').val());
        formData.append('customer_pass', $('#customer_pass').val());
        formData.append('confirm_password', $('#confirm_password').val());
        
        // Add image file if selected (optional)
        if ($('#customer_image')[0].files[0]) {
            formData.append('customer_image', $('#customer_image')[0].files[0]);
        }

        // Show loading indicator
        Swal.fire({
            title: 'Processing...',
            text: 'Please wait while we create your account',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Perform the AJAX request to register the user
        $.ajax({
            url: '../Login/register_process.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                Swal.close();
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Registration Successful',
                        text: response.message
                    }).then(function() {
                        window.location.href = 'login.php';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Registration Failed',
                        text: response.message || 'An unknown error occurred'
                    });
                }
            },
            error: function(xhr, status, error) {
                Swal.close();
                console.log("AJAX Error:");
                console.log("Status: " + status);
                console.log("Error: " + error);
                console.log("Response Text: ", xhr.responseText);
                
                let errorMessage = 'An error occurred while processing your request.';
                
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

    function validateForm() {
        var customerName = $('#customer_name').val();
        var email = $('#customer_email').val();
        var country = $('#customer_country').val();
        var city = $('#customer_city').val();
        var phoneNumber = $('#customer_contact').val();
        var password = $('#customer_pass').val();
        var confirmPassword = $('#confirm_password').val();

        if (!customerName || !email || !country || !city || !phoneNumber || !password || !confirmPassword) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'All fields except image are required.'
            });
            return false;
        }

        if (!validateEmail(email)) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Please enter a valid email address.'
            });
            return false;
        }

        if (!validateText(customerName)) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Name cannot contain only numbers.'
            });
            return false;
        }

        if (!validateText(country)) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Country cannot contain only numbers.'
            });
            return false;
        }

        if (!validateText(city)) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'City cannot contain only numbers.'
            });
            return false;
        }

        if (!validateContact(phoneNumber)) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Please enter a valid phone number.'
            });
            return false;
        }

        if (password !== confirmPassword) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Passwords do not match.'
            });
            return false;
        }

        return true;
    }

    function validateEmail(email) {
        var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    function validateText(text) {
        var re = /^(?!\d+$)[a-zA-Z\d\s]+$/;
        return re.test(text);
    }

    function validateContact(contact) {
        // Modified to accept more standard phone formats
        var re = /^\+?[0-9]{10,15}$/;
        return re.test(contact);
    }
});