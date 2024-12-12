<footer>
    <div class="footer-content">
        <div class="footer-item">&copy; <?php echo date("Y"); ?> Soup Boi Athletics</div>
        <div class="footer-item">Contact us: <a href="mailto:rodgers.timothyj@gmail.com">info@soupboi.com</a></div>
        <div class="footer-item" id="google_translate_element"></div>
        <div class="footer-item accessibility-options">
            <button onclick="toggleContrast()">Toggle High Contrast</button>
            <button onclick="increaseFontSize()">Increase Font Size</button>
            <button onclick="decreaseFontSize()">Decrease Font Size</button>
            <button onclick="resetAccessibility()">Reset</button>
        </div>
    </div>
</footer>
<script>
let currentFontSize = 1;

function toggleContrast() {
    document.body.classList.toggle('high-contrast');
}

function increaseFontSize() {
    currentFontSize += 0.1;
    document.documentElement.style.fontSize = currentFontSize + 'em';
}

function decreaseFontSize() {
    currentFontSize -= 0.1;
    document.documentElement.style.fontSize = currentFontSize + 'em';
}

function resetAccessibility() {
    document.body.classList.remove('high-contrast');
    currentFontSize = 1;
    document.documentElement.style.fontSize = '1em';
}

function googleTranslateElementInit() {
    new google.translate.TranslateElement({pageLanguage: 'en'}, 'google_translate_element');
}
</script>
<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
<style>
.footer-content {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    align-items: center;
    padding: 10px;
    background-color: #b5c84c;
}

.footer-item {
    margin: 0 15px;
}

.high-contrast {
    background-color: black;
    color: white;
}

.accessibility-options button,
.accessibility-options select {
    margin: 5px;
}
</style>
</body>
</html>



