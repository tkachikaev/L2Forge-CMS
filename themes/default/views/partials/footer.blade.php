<footer class="site-footer">
    <div class="container footer-grid">
        <div><div class="brand footer-brand"><span class="brand-mark">L2</span><span><strong>{{ config('app.name') }}</strong><small>OPEN SOURCE CMS</small></span></div><p>Независимый игровой проект без платных преимуществ.</p></div>
        <div><h3>Навигация</h3><a href="{{ route('news.index') }}">Новости</a><a href="{{ route('downloads') }}">Скачать клиент</a><a href="{{ route('about') }}">Описание сервера</a></div>
        <div><h3>Документы</h3><a href="#">Правила</a><a href="#">Конфиденциальность</a><a href="#">Контакты</a></div>
        <div><h3>Сообщество</h3><div class="socials"><a href="#">VK</a><a href="#">Discord</a><a href="#">Telegram</a></div></div>
    </div>
    <div class="container footer-bottom"><span>© {{ date('Y') }} {{ config('app.name') }}</span><span>Lineage II является товарным знаком соответствующих правообладателей.</span></div>
</footer>
