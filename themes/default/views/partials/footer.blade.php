<footer class="site-footer">
    <div class="container footer-grid">
        <div>
            <div class="brand footer-brand">
                @if (site_logo_url())
                    <img class="brand-logo footer-brand-logo" src="{{ site_logo_url() }}" alt="{{ site_name() }}">
                @else
                    <span class="brand-mark">L2</span><span><strong>{{ site_name() }}</strong><small>OPEN SOURCE CMS</small></span>
                @endif
            </div>
            @if (site_description() !== '')
                <p>{{ site_description() }}</p>
            @endif
        </div>
        <div><h3>Навигация</h3><a href="{{ route('news.index') }}">Новости</a><a href="{{ route('downloads') }}">Скачать клиент</a><a href="{{ route('about') }}">Описание сервера</a></div>
        <div><h3>Документы</h3><a href="#">Правила</a><a href="#">Конфиденциальность</a><a href="#">Контакты</a></div>
        <div><h3>Сообщество</h3><div class="socials"><a href="#">VK</a><a href="#">Discord</a><a href="#">Telegram</a></div></div>
    </div>
    <div class="container footer-bottom">
        <span>{{ site_footer_text() }}</span>
        <span>Lineage II является товарным знаком соответствующих правообладателей.</span>
    </div>
</footer>
