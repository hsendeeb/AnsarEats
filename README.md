# AnsarEats 🍕

**AnsarEats** is a premium, high-performance food marketplace and delivery platform built for modern dining experiences. It connects local restaurants, bakeries, and markets directly with hungry customers through a sleek, fast, and feature-rich interface.

![AnsarEats Hero](https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&q=80&w=1200&h=400)

## ✨ Features

- 🌓 **Premium Dark Mode**: A stunning iPhone-style theme toggle with automatic system preference detection and zero-flash loading.
- 🔍 **Smart Explorer**: Real-time search suggestions with split results for restaurants and specific meals.
- ⚡ **Dynamic Home Page**: High-impact "Trending Spots" section featuring the most popular local eateries.
- 🛒 **Advanced Shopping Experience**: 
    - Smooth "Add to Cart" animations with GSAP and Lottie.
    - Persistence for both guest and authenticated users.
    - Variant-aware cart items (size, toppings, etc.).
- ⏱️ **Live Order Tracking**: 
    - Real-time preparation time visibility for customers.
    - Visual status updates (Pending → Accepted → Preparing → Delivered).
- 📊 **Owner Dashboard**:
    - Comprehensive order management for restaurant partners.
    - Live preparation tracking and order acceptance workflow.
    - Performance metrics and history.
- 📱 **Mobile-First Design**: Fully responsive navigation drawer and mobile-optimized interfaces for ordering on the go.
- ⭐ **Rating System**: Integrated restaurant rating and review system to maintain high quality standards.

## 🛠️ Technology Stack

- **Backend**: [Laravel 11](https://laravel.com/) (PHP 8.2+)
- **Frontend**: [Tailwind CSS](https://tailwindcss.com/) & [Alpine.js](https://alpinejs.dev/)
- **Animations**: [GSAP](https://greensock.com/gsap/) & [Lottie](https://lottiefiles.com/)
- **State Management**: Alpine Global Stores & LocalStorage
- **Database**: MySQL/PostgreSQL

## 🚀 Getting Started

### Prerequisites

- PHP 8.2+
- Composer
- Node.js & NPM
- MySQL or SQLite

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/hsendeeb/AnsarEats.git
   cd AnsarEats
   ```

2. **Install Dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database Configuration**
   Configure your database settings in the `.env` file, then run migrations:
   ```bash
   php artisan migrate --seed
   ```

5. **Link Storage**
   ```bash
   php artisan storage:link
   ```

6. **Start the Development Server**
   ```bash
   # Run Vite for frontend assets
   npm run dev
   
   # Run Laravel server
   php artisan serve
   ```

## 📖 Key Modules

### Advanced Search Suggestions
The search engine provides instantaneous results for both restaurants and specific meals, using a custom suggestions controller to optimize throughput and user experience.

### Dark Mode Management
Uses a hybrid approach (CSS variables + Tailwind `dark:` class) with a Global Alpine store (`$store.darkMode`) to sync state across the header, sidebar, and dashboard components.

### Order Preparation Timer
Calculates and displays preparation times dynamically based on order timestamps and restaurant-defined estimates.

## 📄 License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details.

---

<p align="center">Crafted with ❤️ by the AnsarEats Team</p>
