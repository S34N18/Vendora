@extends('layouts.app')

@section('title', 'Checkout')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/checkout.css') }}">
@endsection

@section('content')
<div class="container py-5">
    <div class="row">
        <!-- Cart Summary (Left Side) -->
        <div class="col-lg-7">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    @if(empty($cartItems))
                        <div class="empty-cart">
                            <i class="fas fa-shopping-cart empty-cart-icon"></i>
                            <h5 class="text-muted">Your cart is empty</h5>
                            <a href="{{ route('products.index') }}" class="btn btn-primary">Continue Shopping</a>
                        </div>
                    @else
                        @foreach($cartItems as $item)
                        <div class="row align-items-center product-item">
                            <div class="col-md-2">
                                @if($item['product']->image)
                                    <img src="{{ asset('storage/' . $item['product']->image) }}" 
                                         alt="{{ $item['product']->name }}" 
                                         class="product-image">
                                @else
                                    <div class="product-placeholder">
                                        <i class="fas fa-image"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <h6 class="mb-1">{{ $item['product']->name }}</h6>
                                <p class="text-muted small mb-0">{{ $item['product']->description }}</p>
                            </div>
                            <div class="col-md-2 text-center">
                                <span class="fw-bold">{{ $item['quantity'] }}</span>
                            </div>
                            <div class="col-md-2 text-end">
                                <span class="fw-bold">KSh {{ isset($item['total']) ? number_format($item['total'], 2) : '0.00' }}</span>
                            </div>
                        </div>
                        @endforeach
                        
                        <!-- Order Totals -->
                        <div class="order-totals">
                            <div class="total-row">
                                <span>Subtotal:</span>
                                <span>KSh {{ number_format($totals['subtotal'], 2) }}</span>
                            </div>
                            <div class="total-row">
                                <span>VAT (16%):</span>
                                <span>KSh {{ number_format($totals['tax_amount'], 2) }}</span>
                            </div>
                            <div class="total-row">
                                <span>Shipping:</span>
                                <span class="text-success">FREE</span>
                            </div>
                            <div class="total-row">
                                <span>Total:</span>
                                <span class="total-amount">KSh {{ number_format($totals['total'], 2) }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Checkout Form (Right Side) -->
        <div class="col-lg-5">
            @if(!empty($cartItems))
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Billing & Shipping Details</h5>
                </div>
                <div class="card-body">
                    <form id="checkout-form">
                        @csrf
                        
                        <!-- Customer Information -->
                        <div class="mb-4">
                            <h6 class="text-muted mb-3">Customer Information</h6>
                            
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name *</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="name" 
                                       name="name" 
                                       value="{{ $user->name ?? '' }}" 
                                       required>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       value="{{ $user->email ?? '' }}" 
                                       required>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number *</label>
                                <input type="tel" 
                                       class="form-control" 
                                       id="phone" 
                                       name="phone" 
                                       placeholder="0712345678"
                                       required>
                                <div class="invalid-feedback"></div>
                                <div class="form-text">Enter your M-Pesa phone number</div>
                            </div>
                        </div>
                        
                        <!-- Shipping Information -->
                        <div class="mb-4">
                            <h6 class="text-muted mb-3">Shipping Address</h6>
                            
                            <div class="mb-3">
                                <label for="address" class="form-label">Street Address *</label>
                                <textarea class="form-control" 
                                          id="address" 
                                          name="address" 
                                          rows="2" 
                                          placeholder="Enter your full address"
                                          required></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="city" class="form-label">City *</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="city" 
                                           name="city" 
                                           required>
                                    <div class="invalid-feedback"></div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="county" class="form-label">County *</label>
                                    <select class="form-control form-select" id="county" name="county" required>
                                        <option value="">Select County</option>
                                        <option value="Nairobi">Nairobi</option>
                                        <option value="Mombasa">Mombasa</option>
                                        <option value="Kiambu">Kiambu</option>
                                        <option value="Nakuru">Nakuru</option>
                                        <option value="Kisumu">Kisumu</option>
                                        <option value="Eldoret">Eldoret</option>
                                        <option value="Thika">Thika</option>
                                        <option value="Malindi">Malindi</option>
                                        <option value="Kitale">Kitale</option>
                                        <option value="Garissa">Garissa</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Payment Information -->
                        <div class="mb-4">
                            <h6 class="text-muted mb-3">Payment Method</h6>
                            <div class="card payment-method-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <img src="https://upload.wikimedia.org/wikipedia/commons/1/15/M-PESA_LOGO-01.svg" 
                                             alt="M-Pesa" 
                                             class="mpesa-logo me-3">
                                        <div>
                                            <h6 class="mb-0 text-white">M-Pesa Payment</h6>
                                            <small class="text-white-50">Pay securely with your M-Pesa account</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Order Notes -->
                        <div class="mb-4">
                            <label for="notes" class="form-label">Order Notes (Optional)</label>
                            <textarea class="form-control" 
                                      id="notes" 
                                      name="notes" 
                                      rows="2" 
                                      placeholder="Any special instructions for your order"></textarea>
                        </div>
                        
                        <!-- Submit Button -->
                        <button type="submit" id="checkout-btn" class="btn btn-primary btn-lg w-100" style="width: 100%;">
                            <span class="btn-text">
                                <i class="fas fa-credit-card me-2"></i>Place Order - KSh {{ number_format($totals['total'], 2) }}
                            </span>
                            <span class="btn-loading d-none">
                                <i class="fas fa-spinner fa-spin me-2"></i>Processing...
                            </span>
                        </button>
                        
                        <div class="text-center security-badge">
                            <i class="fas fa-lock"></i>
                            Your payment information is secure and encrypted
                        </div>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">M-Pesa Payment</h5>
            </div>
            <div class="modal-body text-center">
                <div id="payment-status" class="payment-pending">
                    <div class="payment-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h5>Payment Request Sent</h5>
                    <p class="text-muted">Please check your phone for the M-Pesa payment request and enter your PIN to complete the payment.</p>
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="small text-muted mt-2">Waiting for payment confirmation...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" id="cancel-payment">Cancel</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
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
    // Real-time validation
    function validateField(fieldName, value) {
        const rule = validationRules[fieldName];
        if (!rule) return true;
        address: {
        const field = $(`#${fieldName}`);
        const feedback = field.siblings('.invalid-feedback');
            required: true,
        // Check if required
        if (rule.required && (!value || value.trim() === '')) {
            showFieldError(field, feedback, rule.message);
            return false;
        }
            minLength: 10,
        // Check minimum length
        if (rule.minLength && value.length < rule.minLength) {
            showFieldError(field, feedback, rule.message);
            return false;
        }
            message: 'Please enter your complete address (minimum 10 characters)'
        // Check pattern
        if (rule.pattern && !rule.pattern.test(value)) {
            showFieldError(field, feedback, rule.message);
            return false;
        }
        },
        // Field is valid
        showFieldSuccess(field, feedback);
        return true;
    }
        city: {
    function showFieldError(field, feedback, message) {
        field.removeClass('is-valid').addClass('is-invalid');
        feedback.text(message);
    }
            required: true,
    function showFieldSuccess(field, feedback) {
        field.removeClass('is-invalid').addClass('is-valid');
        feedback.text('');
    }
            minLength: 2,
    // Attach real-time validation to form fields
    Object.keys(validationRules).forEach(fieldName => {
        $(`#${fieldName}`).on('blur keyup', function() {
            const value = $(this).val();
            validateField(fieldName, value);
        });
    });
            message: 'Please enter your city'
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
        },
        // Add optional fields
        formData.notes = $('#notes').val();
        county: {
        if (!isValid) {
            showAlert('Please correct the errors in the form before proceeding.', 'error');
            return;
        }
            required: true,
        // Show loading state
        showLoadingState(true);
        
        // Submit the order
        submitOrder(formData);
    });
            message: 'Please select your county'
    function submitOrder(formData) {
        $.ajax({
            url: '/checkout/process',
            method: 'POST',
            data: {
                ...formData,
                _token: $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val()
            },
            success: function(response) {
                if (response.success) {
                    // Redirect to success page
                    window.location.href = response.redirect_url || '/checkout/success/' + response.order_id;
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
        }
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
    };
    function showAlert(message, type = 'info') {
        // Remove existing alerts
        $('.alert-checkout').remove();
        
        const alertClass = {
            'success': 'alert-success',
            'error': 'alert-danger',
            'info': 'alert-info',
            'warning': 'alert-warning'
        };
        
        const alert = $(`
            <div class="alert ${alertClass[type]} alert-dismissible fade show alert-checkout" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        
        // Insert alert at the top of the container
        $('.container.py-5').prepend(alert);
        
        // Scroll to top to show alert
        $('html, body').animate({ scrollTop: 0 }, 300);
    }
});
</script>
@endsection