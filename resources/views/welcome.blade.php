<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel 演示项目') }}</title>

        <!-- 使用系统字体，避免外部字体依赖 -->
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                line-height: 1.6;
                color: #333;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
            }

            .container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 20px;
            }

            .header {
                text-align: center;
                padding: 40px 0;
                color: white;
            }

            .logo {
                width: 80px;
                height: 80px;
                background: linear-gradient(45deg, #ff2d20, #ff6b6b);
                border-radius: 16px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: 32px;
                font-weight: bold;
                color: white;
                margin-bottom: 20px;
                box-shadow: 0 10px 30px rgba(255, 45, 32, 0.3);
            }

            .title {
                font-size: 3rem;
                font-weight: 700;
                margin-bottom: 10px;
                text-shadow: 0 2px 10px rgba(0,0,0,0.2);
            }

            .subtitle {
                font-size: 1.2rem;
                opacity: 0.9;
                margin-bottom: 30px;
            }

            .nav {
                display: flex;
                justify-content: center;
                gap: 20px;
                margin-bottom: 50px;
            }

            .nav-link {
                padding: 12px 24px;
                background: rgba(255,255,255,0.1);
                color: white;
                text-decoration: none;
                border-radius: 8px;
                border: 1px solid rgba(255,255,255,0.2);
                transition: all 0.3s ease;
                backdrop-filter: blur(10px);
            }

            .nav-link:hover {
                background: rgba(255,255,255,0.2);
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            }

            .features {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 30px;
                margin: 50px 0;
            }

            .feature-card {
                background: rgba(255,255,255,0.95);
                padding: 30px;
                border-radius: 16px;
                box-shadow: 0 20px 40px rgba(0,0,0,0.1);
                transition: all 0.3s ease;
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255,255,255,0.2);
            }

            .feature-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 25px 50px rgba(0,0,0,0.15);
            }

            .feature-icon {
                width: 60px;
                height: 60px;
                background: linear-gradient(45deg, #ff2d20, #ff6b6b);
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 24px;
                color: white;
                margin-bottom: 20px;
            }

            .feature-title {
                font-size: 1.5rem;
                font-weight: 600;
                margin-bottom: 15px;
                color: #333;
            }

            .feature-description {
                color: #666;
                line-height: 1.6;
            }

            .tech-stack {
                background: rgba(255,255,255,0.95);
                padding: 40px;
                border-radius: 16px;
                margin: 50px 0;
                text-align: center;
            }

            .tech-title {
                font-size: 2rem;
                font-weight: 600;
                margin-bottom: 30px;
                color: #333;
            }

            .tech-items {
                display: flex;
                justify-content: center;
                flex-wrap: wrap;
                gap: 20px;
            }

            .tech-item {
                background: linear-gradient(45deg, #667eea, #764ba2);
                color: white;
                padding: 10px 20px;
                border-radius: 25px;
                font-size: 14px;
                font-weight: 500;
                box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            }

            .modules-section {
                background: rgba(255,255,255,0.95);
                padding: 40px;
                border-radius: 16px;
                margin: 50px 0;
                text-align: center;
            }

            .modules-title {
                font-size: 2rem;
                font-weight: 600;
                margin-bottom: 30px;
                color: #333;
            }

            .modules-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 20px;
                margin-top: 30px;
            }

            .module-item {
                background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
                padding: 20px;
                border-radius: 12px;
                text-align: center;
                transition: all 0.3s ease;
            }

            .module-item:hover {
                transform: translateY(-3px);
                box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            }

            .module-name {
                font-weight: 600;
                color: #333;
                margin-bottom: 5px;
            }

            .module-status {
                font-size: 12px;
                color: #666;
            }

            .footer {
                text-align: center;
                padding: 40px 0;
                color: rgba(255,255,255,0.8);
            }

            .version-info {
                background: rgba(0,0,0,0.2);
                padding: 10px 20px;
                border-radius: 20px;
                display: inline-block;
                font-family: 'Courier New', monospace;
                font-size: 14px;
            }

            /* 倒计时跳转样式 */
            .redirect-notice {
                position: fixed;
                bottom: 20px;
                right: 20px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 20px;
                border-radius: 12px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.3);
                max-width: 350px;
                z-index: 1000;
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255,255,255,0.1);
            }

            .redirect-header {
                display: flex;
                align-items: center;
                margin-bottom: 15px;
                font-weight: 600;
                font-size: 16px;
            }

            .redirect-icon {
                font-size: 24px;
                margin-right: 10px;
                animation: pulse 1s infinite;
            }

            .redirect-content {
                margin-bottom: 15px;
                font-size: 14px;
                opacity: 0.9;
                line-height: 1.5;
            }

            .redirect-countdown {
                display: flex;
                align-items: center;
                margin-bottom: 15px;
                font-size: 18px;
                font-weight: bold;
            }

            .countdown-number {
                color: #ff6b6b;
                font-size: 24px;
                margin: 0 5px;
            }

            .redirect-progress {
                width: 100%;
                height: 4px;
                background: rgba(255,255,255,0.2);
                border-radius: 2px;
                overflow: hidden;
                margin-bottom: 15px;
            }

            .progress-bar {
                height: 100%;
                background: linear-gradient(90deg, #ff6b6b, #ff2d20);
                border-radius: 2px;
                transition: width 1s linear;
            }

            .redirect-actions {
                display: flex;
                gap: 10px;
                justify-content: space-between;
            }

            .redirect-btn {
                flex: 1;
                padding: 8px 16px;
                border: none;
                border-radius: 6px;
                cursor: pointer;
                font-size: 14px;
                transition: all 0.3s;
                text-align: center;
            }

            .btn-redirect {
                background: #ff6b6b;
                color: white;
            }

            .btn-cancel {
                background: rgba(255,255,255,0.2);
                color: white;
                border: 1px solid rgba(255,255,255,0.3);
            }

            .redirect-btn:hover {
                transform: translateY(-1px);
                box-shadow: 0 3px 10px rgba(0,0,0,0.2);
            }

            .target-url {
                font-family: monospace;
                background: rgba(255,255,255,0.1);
                padding: 2px 6px;
                border-radius: 4px;
                font-size: 12px;
                word-break: break-all;
            }

            @keyframes pulse {
                0% { transform: scale(1); }
                50% { transform: scale(1.1); }
                100% { transform: scale(1); }
            }

            @media (max-width: 768px) {
                .redirect-notice {
                    bottom: 10px;
                    right: 10px;
                    left: 10px;
                    max-width: none;
                }

                .redirect-actions {
                    flex-direction: column;
                }
            }

            @media (max-width: 768px) {
                .title {
                    font-size: 2rem;
                }

                .nav {
                    flex-direction: column;
                    align-items: center;
                }

                .features {
                    grid-template-columns: 1fr;
                }

                .tech-items {
                    flex-direction: column;
                    align-items: center;
                }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <header class="header">
                <div class="logo">L</div>
                <h1 class="title">{{ config('app.name', 'Laravel 演示项目') }}</h1>
                <p class="subtitle">基于 Laravel 12 + Dcat Admin 2 的现代化管理系统</p>

                
            </header>

            <main>
                <section class="features">
                    <div class="feature-card">
                        <div class="feature-icon">📚</div>
                        <h2 class="feature-title">完整文档</h2>
                        <p class="feature-description">
                            Laravel 拥有出色的文档，涵盖框架的每个方面。无论您是新手还是经验丰富的开发者，都建议从头到尾阅读我们的文档。
                        </p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">🎓</div>
                        <h2 class="feature-title">视频教程</h2>
                        <p class="feature-description">
                            提供数千个关于 Laravel、PHP 和 JavaScript 开发的视频教程。通过学习大幅提升您的开发技能。
                        </p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">📰</div>
                        <h2 class="feature-title">社区资讯</h2>
                        <p class="feature-description">
                            社区驱动的门户和新闻通讯，汇集 Laravel 生态系统中所有最新和最重要的新闻，包括新包发布和教程。
                        </p>
                    </div>
                </section>

                <section class="tech-stack">
                    <h2 class="tech-title">技术栈</h2>
                    <div class="tech-items">
                        <div class="tech-item">PHP 8.3</div>
                        <div class="tech-item">Laravel 12</div>
                        <div class="tech-item">Dcat Admin 2</div>
                        <div class="tech-item">MySQL 8.0</div>
                        <div class="tech-item">nwidart/laravel-modules</div>
                    </div>
                </section>

              </main>

            </footer>

            <!-- 倒计时跳转功能 -->
            @if(config('app.web_index'))
                <div id="redirect-notice" class="redirect-notice">
                    <div class="redirect-header">
                        <span class="redirect-icon">⏰</span>
                        <span>即将跳转</span>
                    </div>
                    <div class="redirect-content">
                        页面将在 <span id="countdown" class="countdown-number">10</span> 秒后自动跳转到目标页面
                    </div>
                    <div class="redirect-content">
                        目标地址: <span class="target-url">{{ config('app.web_index') }}</span>
                    </div>
                    <div class="redirect-progress">
                        <div id="progress-bar" class="progress-bar" style="width: 100%;"></div>
                    </div>
                    <div class="redirect-actions">
                        <button onclick="immediateRedirect()" class="redirect-btn btn-redirect">立即跳转</button>
                        <button onclick="cancelRedirect()" class="redirect-btn btn-cancel">取消跳转</button>
                    </div>
                </div>

                <script>
                    let countdown = 10;
                    const countdownElement = document.getElementById('countdown');
                    const progressBar = document.getElementById('progress-bar');
                    const redirectNotice = document.getElementById('redirect-notice');
                    const targetUrl = '{{ config('app.web_index') }}';

                    function updateCountdown() {
                        countdown--;
                        countdownElement.textContent = countdown;

                        // 更新进度条：10秒对应100%，0秒对应0%
                        progressBar.style.width = (countdown * 10) + '%';

                        if (countdown <= 0) {
                            setTimeout(() => {
                                immediateRedirect();
                            }, 100); // 给用户100ms看到0秒
                            return;
                        }
                    }

                    function immediateRedirect() {
                        window.location.href = targetUrl;
                    }

                    function cancelRedirect() {
                        redirectNotice.style.display = 'none';
                        clearInterval(window.countdownInterval);
                    }

                    // 开始倒计时 - 先更新一次显示
                    updateCountdown();
                    window.countdownInterval = setInterval(() => {
                        updateCountdown();
                        if (countdown <= 0) {
                            clearInterval(window.countdownInterval);
                        }
                    }, 1000);
                </script>
            @endif
        </div>
    </body>
</html>