<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Mark\'s Online Store')</title>
    
    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Custom Styles -->
    <link rel="stylesheet" href="{{ asset('css/custom-styles.css') }}">
    
    <!-- Page-specific styles -->
    @stack('styles')
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen">
    <!-- Header -->
    <header class="header-main">
        <div class="header-container">
            <div class="header-content">
                <!-- Logo -->
                <div class="logo-section">
                    <a href="/" class="logo-link">
                        <div class="logo-icon">
                            <span class="logo-letter">M</span>
                        </div>
                        <h1 class="logo-text">Mark's Store</h1>
                    </a>
                </div>
                        <h1 class="logo-text">Vendora Supermarket</h1>
                    </a>
                </div>

                <!-- Desktop Navigation -->
                <nav class="desktop-nav">
                    <a href="/" class="nav-link">Home</a>
                    <a href="/products" class="nav-link">Products</a>
                    <a href="/cart" class="nav-link cart-link">
                        Cart
                        <!-- Dynamic Cart badge -->
                        <span class="cart-badge" id="cart-count">
                            {{ session('cart') ? array_sum(array_column(session('cart'), 'quantity')) : 0 }}
                        </span>
                    </a>
                    
                    @auth
                        <a href="/dashboard" class="nav-link">Dashboard</a>
                        <div class="user-dropdown">
                            <div class="user-info" onclick="toggleUserDropdown()">
                                <div class="user-avatar">
                                    <span class="user-initial">{{ substr(Auth::user()->name, 0, 1) }}</span>
                                </div>
                                <span class="user-name">{{ Auth::user()->name }}</span>
                                <svg class="dropdown-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                            <div id="userDropdown" class="user-dropdown-menu">
                                <a href="{{ route('profile.edit') }}" class="dropdown-item">
                                    👤 Profile
                                </a>
                                @if(Auth::user()->isAdmin())
                                    <a href="{{ route('admin.dashboard') }}" class="dropdown-item">
                                        🛠️ Admin Panel
                                    </a>
                                @endif
                                <div class="dropdown-divider"></div>
                                <form method="POST" action="{{ route('logout') }}" class="dropdown-logout-form">
                                    @csrf
                                    <button type="submit" class="dropdown-item logout-btn">
                                        🚪 Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="auth-buttons">
                            <a href="/login" class="nav-link">Login</a>
                            <a href="/register" class="register-btn">
                                Register
                            </a>
                        </div>
                    @endauth
                </nav>

                <!-- Mobile menu button -->
                <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
                    <svg class="menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>

            <!-- Mobile Navigation -->
            <div id="mobileMenu" class="mobile-nav">
                <div class="mobile-nav-content">
                    <a href="/" class="mobile-nav-link">Home</a>
                    <a href="/products" class="mobile-nav-link">Products</a>
                    <a href="/cart" class="mobile-nav-link">
                        Cart (<span id="mobile-cart-count">{{ session('cart') ? array_sum(array_column(session('cart'), 'quantity')) : 0 }}</span>)
                    </a>
                    
                    @auth
                        <a href="/dashboard" class="mobile-nav-link">Dashboard</a>
                        <div class="mobile-user-section">
                            <div class="mobile-user-info">
                                <div class="user-avatar">
                                    <span class="user-initial">{{ substr(Auth::user()->name, 0, 1) }}</span>
                                </div>
                                <span class="user-name">{{ Auth::user()->name }}</span>
                            </div>
                            <div class="mobile-user-actions">
                                <a href="{{ route('profile.edit') }}" class="mobile-nav-link">
                                    👤 Profile
                                </a>
                                @if(Auth::user()->isAdmin())
                                    <a href="{{ route('admin.dashboard') }}" class="mobile-nav-link">
                                        🛠️ Admin Panel
                                    </a>
                                @endif
                                <form method="POST" action="{{ route('logout') }}" class="mobile-logout-form">
                                    @csrf
                                    <button type="submit" class="mobile-logout-btn">
                                        🚪 Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="mobile-auth-section">
                            <a href="/login" class="mobile-nav-link">Login</a>
                            <a href="/register" class="mobile-register-btn">
                                Register
                            </a>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer-main">
        <div class="footer-container">
            <div class="footer-grid">
                <!-- Company Info -->
                <div class="footer-company">
                    <div class="footer-logo">
                        <div class="logo-icon">
                            <span class="logo-letter">V</span>
                        </div>
                        <h3 class="footer-logo-text">Vendora Supermarket</h3>
                    </div>
                    <p class="footer-description">Your trusted online destination for quality products at great prices. We're committed to providing excellent service and customer satisfaction.</p>
                    <div class="social-links">
                        <a href="#" class="social-link">
                            <span class="sr-only">Facebook</span>
                            <svg class="social-icon" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <a href="#" class="social-link">
                            <span class="sr-only">Twitter</span>
                            <svg class="social-icon" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                        </a>
                        <a href="#" class="social-link">
                            <span class="sr-only">Instagram</span>
                            <svg class="social-icon" fill="currentColor" viewBox="0 0 24 24"><path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 6.621 5.367 11.988 11.988 11.988s11.987-5.367 11.987-11.988C24.014 5.367 18.647.001 12.017.001zM8.449 16.988c-1.297 0-2.348-1.051-2.348-2.348s1.051-2.348 2.348-2.348 2.348 1.051 2.348 2.348-1.051 2.348-2.348 2.348zm7.718 0c-1.297 0-2.348-1.051-2.348-2.348s1.051-2.348 2.348-2.348 2.348 1.051 2.348 2.348-1.051 2.348-2.348 2.348z"/></svg>
                        </a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="footer-section">
                    <h4 class="footer-title">Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="/" class="footer-link">Home</a></li>
                        <li><a href="/products" class="footer-link">Products</a></li>
                        <li><a href="#" class="footer-link">About Us</a></li>
                        <li><a href="#" class="footer-link">Contact</a></li>
                    </ul>
                </div>
                
                <!-- Customer Service -->
                <div class="footer-section">
                    <h4 class="footer-title">Customer Service</h4>
                    <ul class="footer-links">
                        <li><a href="#" class="footer-link">Help Center</a></li>
                        <li><a href="#" class="footer-link">Returns</a></li>
                        <li><a href="#" class="footer-link">Shipping Info</a></li>
                        <li><a href="#" class="footer-link">Privacy Policy</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p class="footer-copyright">&copy; {{ date('Y') }} Mark's Store. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Additional CSS for user dropdown -->
    <style>
        .user-dropdown {
            position: relative;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.5rem;
            transition: background-color 0.3s ease;
        }

        .user-info:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .dropdown-arrow {
            width: 1rem;
            height: 1rem;
            transition: transform 0.3s ease;
        }

        .user-info.active .dropdown-arrow {
            transform: rotate(180deg);
        }

        .user-dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            min-width: 12rem;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
        }

        .user-dropdown-menu.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-item {
            display: block;
            padding: 0.75rem 1rem;
            color: #374151;
            text-decoration: none;
            transition: background-color 0.3s ease;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            cursor: pointer;
        }

        .dropdown-item:hover {
            background-color: #f3f4f6;
        }

        .dropdown-divider {
            height: 1px;
            background-color: #e5e7eb;
            margin: 0.5rem 0;
        }

        .dropdown-logout-form {
            margin: 0;
        }

        .logout-btn {
            color: #ef4444;
            font-weight: 500;
        }

        .logout-btn:hover {
            background-color: #fef2f2;
        }

        /* Mobile styles */
        .mobile-user-section {
            padding: 1rem 0;
            border-top: 1px solid #e5e7eb;
        }

        .mobile-user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
            padding: 0 1rem;
        }

        .mobile-user-actions {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .mobile-logout-form {
            margin: 0;
        }

        .mobile-logout-btn {
            display: block;
            width: 100%;
            padding: 0.75rem 1rem;
            background: #ef4444;
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin: 0.5rem 1rem;
        }

        .mobile-logout-btn:hover {
            background: #dc2626;
        }

        @media (max-width: 768px) {
            .user-dropdown-menu {
                right: auto;
                left: 0;
                min-width: 100%;
            }
        }
    </style>

    <!-- Scripts -->
    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            const button = document.querySelector('.mobile-menu-btn');
            
            menu.classList.toggle('active');
            button.classList.toggle('active');
        }

        function toggleUserDropdown() {
            const dropdown = document.getElementById('userDropdown');
            const userInfo = document.querySelector('.user-info');
            
            dropdown.classList.toggle('show');
            userInfo.classList.toggle('active');
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            const menu = document.getElementById('mobileMenu');
            const button = document.querySelector('.mobile-menu-btn');
            const userDropdown = document.getElementById('userDropdown');
            const userInfo = document.querySelector('.user-info');
            
            // Close mobile menu
            if (!button.contains(event.target) && !menu.contains(event.target)) {
                menu.classList.remove('active');
                button.classList.remove('active');
            }
            
            // Close user dropdown
            if (userDropdown && userInfo && !userInfo.contains(event.target) && !userDropdown.contains(event.target)) {
                userDropdown.classList.remove('show');
                userInfo.classList.remove('active');
            }
        });

        // Add active class to current page nav link
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname;
            const navLinks = document.querySelectorAll('.nav-link');
            
            navLinks.forEach(link => {
                if (link.getAttribute('href') === currentPath) {
                    link.classList.add('active');
                }
            });
        });

        // Add logout confirmation
        document.addEventListener('DOMContentLoaded', function() {
            const logoutForms = document.querySelectorAll('.dropdown-logout-form, .mobile-logout-form');
            
            logoutForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    if (!confirm('Are you sure you want to logout?')) {
                        e.preventDefault();
                    }
                });
            });
        });

        // Global cart functions
        function updateCartCount(count) {
            const cartCount = document.getElementById('cart-count');
            const mobileCartCount = document.getElementById('mobile-cart-count');
            
            if (cartCount) cartCount.textContent = count;
            if (mobileCartCount) mobileCartCount.textContent = count;
        }

        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.textContent = message;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${type === 'success' ? '#28a745' : '#dc3545'};
                color: white;
                padding: 15px 20px;
                border-radius: 8px;
                z-index: 10000;
                opacity: 0;
                transform: translateY(-20px);
                transition: all 0.3s ease;
                box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.opacity = '1';
                notification.style.transform = 'translateY(0)';
            }, 100);
            
            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transform = 'translateY(-20px)';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
    </script>
    
    <!-- Page-specific scripts -->
    @stack('scripts')
</body>
</html>