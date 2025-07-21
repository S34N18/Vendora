@extends('layouts.app')

@section('title', 'Welcome to Mark\'s Shop')

@section('content')
    <!-- Hero Section -->
    <div class="hero-section">
        <div class="hero-content">
            <h1 class="hero-title">Welcome to Mark's Online Store</h1>
            <p class="hero-subtitle">Discover amazing products at unbeatable prices</p>
            <p class="hero-description">Explore our curated collection of premium items, carefully selected just for you.</p>
            <div class="hero-buttons">
                <a href="{{ url('/products') }}" class="btn btn-primary">Browse Products</a>
                <a href="#featured" class="btn btn-secondary">View Featured</a>
            </div>
        </div>
        <div class="hero-image">
            <div class="placeholder-image">
                <i class="icon-shopping-bag"></i>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <section class="features-section" id="featured">
        <div class="container">
            <h2 class="section-title">Why Choose Mark's Store?</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">🚚</div>
                    <h3>Free Shipping</h3>
                    <p>Free delivery on orders over $50. Fast and reliable shipping worldwide.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">💳</div>
                    <h3>Secure Payment</h3>
                    <p>Your payment information is encrypted and secure with our trusted partners.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🔄</div>
                    <h3>Easy Returns</h3>
                    <p>30-day hassle-free returns. Not satisfied? We'll make it right.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">⭐</div>
                    <h3>Quality Products</h3>
                    <p>Hand-picked items from trusted suppliers. Quality guaranteed.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>Ready to Start Shopping?</h2>
                <p>Join thousands of satisfied customers who trust Mark's Store</p>
                <a href="{{ url('/products') }}" class="btn btn-primary btn-large">Shop Now</a>
            </div>
        </div>
    </section>

    <style>
        /* Hero Section */
        .hero-section {
            display: flex;
            align-items: center;
            min-height: 60vh;
            padding: 60px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            margin: -20px -20px 40px -20px;
        }

        .hero-content {
            flex: 1;
            max-width: 600px;
            margin: 0 auto;
            text-align: center;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            line-height: 1.2;
        }

        .hero-subtitle {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            opacity: 0.9;
            font-weight: 300;
        }

        .hero-description {
            font-size: 1.1rem;
            margin-bottom: 2rem;
            opacity: 0.8;
            line-height: 1.6;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .hero-image {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            max-width: 400px;
            margin: 0 auto;
        }

        .placeholder-image {
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        /* Button Styles */
        .btn {
            display: inline-block;
            padding: 14px 28px;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            text-align: center;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            font-size: 1rem;
            cursor: pointer;
        }

        .btn-primary {
            background: #ff6b6b;
            color: white;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
        }

        .btn-primary:hover {
            background: #ff5252;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 107, 107, 0.4);
        }

        .btn-secondary {
            background: transparent;
            color: white;
            border: 2px solid white;
        }

        .btn-secondary:hover {
            background: white;
            color: #667eea;
            transform: translateY(-2px);
        }

        .btn-large {
            padding: 18px 36px;
            font-size: 1.2rem;
        }

        /* Features Section */
        .features-section {
            padding: 80px 20px;
            background: #f8f9fa;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 3rem;
            color: #333;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .feature-card {
            background: white;
            padding: 2rem;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .feature-card h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #333;
        }

        .feature-card p {
            color: #666;
            line-height: 1.6;
        }

        /* CTA Section */
        .cta-section {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            padding: 80px 20px;
            text-align: center;
            color: white;
        }

        .cta-content h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .cta-content p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-section {
                flex-direction: column;
                text-align: center;
                padding: 40px 20px;
            }

            .hero-title {
                font-size: 2.5rem;
            }

            .hero-subtitle {
                font-size: 1.3rem;
            }

            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }

            .btn {
                width: 100%;
                max-width: 300px;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .section-title {
                font-size: 2rem;
            }

            .cta-content h2 {
                font-size: 2rem;
            }
        }

        /* Icon placeholder */
        .icon-shopping-bag::before {
            content: "🛍️";
        }
    </style>
@endsection