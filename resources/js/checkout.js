$(document).ready(function() {
    // Form validation rules
    const validationRules = {
        name: {
            required: true,
            minLength: 2,
            message: 'Please enter your full name (minimum 2 characters)'
        },
        email: {
            required: true,
            pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
            message: 'Please enter a valid email address'
        },
        phone: {
            required: true,
            pattern: /^(\+254|0)[17]\d{8}$/,
            message: 'Please enter a valid Kenyan phone number (e.g., 0712345678)'
        },
        address: {
            required: true,
            minLength: 10,
            message: 'Please enter your complete address (minimum 10 characters)'
        },
        city: {
            required: true,
            minLength: 2,
            message: 'Please enter your city'
        },
        county: {
            required: true,
            message: 'Please select your county'
        }
    };

    // Real-time validation
    function validateField(fieldName, value) {
        const rule = validationRules[fieldName];
        if (!rule) return true;

        const field = $(`#${fieldName}`);
        const feedback = field.siblings('.invalid-feedback');

        // Check if required
        if (rule.required && (!value || value.trim() === '')) {
            showFieldError(field, feedback, rule.message);
            return false;
        }

        // Check minimum length
        if (rule.minLength && value.length < rule.minLength) {
            showFieldError(field, feedback, rule.message);
            return false;
        }

        // Check pattern
        if (rule.pattern && !rule.pattern.test(value)) {
            showFieldError(field, feedback, rule.message);
            return false;
        }

        // Field is valid
        showFieldSuccess(field, feedback);
        return true;
    }

    function showFieldError(field, feedback, message) {
        field.removeClass('is-valid').addClass('is-invalid');
        feedback.text(message);
    }

    function showFieldSuccess(field, feedback) {
        field.removeClass('is-invalid').addClass('is-valid');
        feedback.text('');
    }

    // Attach real-time validation to form fields
    Object.keys(validationRules).forEach(fieldName => {
        $(`#${fieldName}`).on('blur keyup', function() {
            const value = $(this).val();
            validateField(fieldName, value);
        });
    });

    // Phone number formatting
    $('#phone').on('input', function() {
        let value = $(this).val().replace(/\D/g, ''); // Remove non-digits
        
        // Auto-format phone number
        if (value.startsWith('254')) {
            value = '+' + value;
        } else if (value.startsWith('7') || value.startsWith('1')) {
            value = '0' + value;
        }
        
        $(this).val(value);
    });

    // Form submission
    $('#checkout-form').on('submit', function(e) {
        e.preventDefault();
        
        // Validate all fields
        let isValid = true;
        const formData = {};
        
        Object.keys(validationRules).forEach(fieldName => {
            const value = $(`#${fieldName}`).val();
            formData[fieldName] = value;
            
            if (!validateField(fieldName, value)) {
                isValid = false;
            }
        });

        // Add optional fields
        formData.notes = $('#notes').val();

        if (!isValid) {
            showAlert('Please correct the errors in the form before proceeding.', 'error');
            return;
        }

        // Show loading state
        showLoadingState(true);
        
        // Submit the order
        submitOrder(formData);
    });

    function submitOrder(formData) {
        $.ajax({
            url: '{{ route("checkout.process") }}',
            method: 'POST',
            data: {
                ...formData,
                _token: $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val()
            },
            success: function(response) {
                if (response.success) {
                    // Show payment modal
                    showPaymentModal(response.data);
                    
                    // Start payment polling
                    pollPaymentStatus(response.data.checkout_request_id);
                } else {
                    showAlert(response.message || 'Order processing failed. Please try again.', 'error');
                }
            },
            error: function(xhr, status, error) {
                let message = 'An error occurred while processing your order.';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    // Handle validation errors
                    const errors = xhr.responseJSON.errors;
                    Object.keys(errors).forEach(field => {
                        const fieldElement = $(`#${field}`);
                        const feedback = fieldElement.siblings('.invalid-feedback');
                        showFieldError(fieldElement, feedback, errors[field][0]);
                    });
                    message = 'Please correct the form errors and try again.';
                }
                
                showAlert(message, 'error');
            },
            complete: function() {
                showLoadingState(false);
            }
        });
    }

    function showPaymentModal(paymentData) {
        $('#paymentModal').modal('show');
        
        // Update modal content with payment details
        const modalBody = $('#paymentModal .modal-body');
        modalBody.find('#payment-status').removeClass('payment-success payment-failed').addClass('payment-pending');
        modalBody.find('h5').text('Payment Request Sent');
        modalBody.find('p.text-muted').first().text(
            `Please check your phone (${paymentData.phone_number}) for the M-Pesa payment request and enter your PIN to complete the payment.`
        );
    }

    function pollPaymentStatus(checkoutRequestId) {
        const pollInterval = setInterval(function() {
            $.ajax({
                url: '/checkout/payment-status',
                method: 'POST',
                data: {
                    checkout_request_id: checkoutRequestId,
                    _token: $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val()
                },
                success: function(response) {
                    if (response.status === 'completed') {
                        clearInterval(pollInterval);
                        showPaymentSuccess(response);
                    } else if (response.status === 'failed' || response.status === 'cancelled') {
                        clearInterval(pollInterval);
                        showPaymentFailed(response.message);
                    }
                    // Continue polling if status is still 'pending'
                },
                error: function() {
                    // Continue polling on error (network issues, etc.)
                }
            });
        }, 3000); // Poll every 3 seconds

        // Stop polling after 5 minutes
        setTimeout(function() {
            clearInterval(pollInterval);
            if ($('#paymentModal').hasClass('show')) {
                showPaymentFailed('Payment timeout. Please try again.');
            }
        }, 300000);
    }

    function showPaymentSuccess(response) {
        const statusDiv = $('#payment-status');
        statusDiv.removeClass('payment-pending payment-failed').addClass('payment-success');
        
        statusDiv.html(`
            <div class="payment-icon text-success">
                <i class="fas fa-check-circle"></i>
            </div>
            <h5 class="text-success">Payment Successful!</h5>
            <p class="text-muted">Your order has been placed successfully.</p>
            <p><strong>Order ID:</strong> ${response.order_id}</p>
            <p><strong>Transaction ID:</strong> ${response.transaction_id}</p>
        `);

        // Update modal footer
        $('#paymentModal .modal-footer').html(`
            <button type="button" class="btn btn-success" onclick="window.location.href='/orders/${response.order_id}'">
                View Order
            </button>
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                Continue Shopping
            </button>
        `);

        // Auto-redirect after 5 seconds
        setTimeout(function() {
            window.location.href = `/orders/${response.order_id}`;
        }, 5000);
    }

    function showPaymentFailed(message) {
        const statusDiv = $('#payment-status');
        statusDiv.removeClass('payment-pending payment-success').addClass('payment-failed');
        
        statusDiv.html(`
            <div class="payment-icon text-danger">
                <i class="fas fa-times-circle"></i>
            </div>
            <h5 class="text-danger">Payment Failed</h5>
            <p class="text-muted">${message || 'Your payment could not be processed.'}</p>
            <p class="small">Please try again or contact support if the problem persists.</p>
        `);

        // Update modal footer
        $('#paymentModal .modal-footer').html(`
            <button type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="location.reload()">
                Try Again
            </button>
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                Cancel
            </button>
        `);
    }

    // Cancel payment button
    $(document).on('click', '#cancel-payment', function() {
        $('#paymentModal').modal('hide');
        showAlert('Payment cancelled. You can try again anytime.', 'info');
    });

    // Helper functions
    function showLoadingState(show) {
        const btn = $('#checkout-btn');
        const btnText = btn.find('.btn-text');
        const btnLoading = btn.find('.btn-loading');
        
        if (show) {
            btn.prop('disabled', true);
            btnText.addClass('d-none');
            btnLoading.removeClass('d-none');
        } else {
            btn.prop('disabled', false);
            btnText.removeClass('d-none');
            btnLoading.addClass('d-none');
        }
    }

    function showAlert(message, type = 'info') {
        // Remove existing alerts
        $('.alert-checkout').remove();
        
        const alertClass = {
            'success': 'alert-success',
            'error': 'alert-danger',
            'info': 'alert-info',
            'warning': 'alert-warning'
        };

        const alertIcon = {
            'success': 'fa-check-circle',
            'error': 'fa-exclamation-circle',
            'info': 'fa-info-circle',
            'warning': 'fa-exclamation-triangle'
        };
        
        const alert = $(`
            <div class="alert ${alertClass[type]} alert-dismissible fade show alert-checkout" role="alert">
                <i class="fas ${alertIcon[type]} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        
        // Insert alert at the top of the container
        $('.container.py-5').prepend(alert);
        
        // Scroll to top to show alert
        $('html, body').animate({ scrollTop: 0 }, 300);
        
        // Auto-hide info and success alerts after 5 seconds
        if (type === 'info' || type === 'success') {
            setTimeout(function() {
                alert.alert('close');
            }, 5000);
        }
    }

    // County selection enhancement
    $('#county').select2 && $('#county').select2({
        placeholder: 'Select your county',
        allowClear: false
    });

    // Auto-save form data to prevent loss
    const formFields = ['name', 'email', 'phone', 'address', 'city', 'county', 'notes'];
    
    // Load saved data
    formFields.forEach(field => {
        const savedValue = localStorage.getItem(`checkout_${field}`);
        if (savedValue && !$(`#${field}`).val()) {
            $(`#${field}`).val(savedValue);
        }
    });

    // Save data on change
    formFields.forEach(field => {
        $(`#${field}`).on('change keyup', function() {
            localStorage.setItem(`checkout_${field}`, $(this).val());
        });
    });

    // Clear saved data on successful order
    function clearSavedFormData() {
        formFields.forEach(field => {
            localStorage.removeItem(`checkout_${field}`);
        });
    }

    // Expose clearSavedFormData for use after successful payment
    window.clearCheckoutFormData = clearSavedFormData;
});