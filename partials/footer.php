<footer class="footer">
    <div class="footer-content">
        <div class="footer-left">
            <p>&copy; <span id="year"></span> Inventory Management System. All rights reserved.</p>
        </div>
        <div class="footer-right">
            <a href="#" class="footer-link">Privacy Policy</a>
            <a href="#" class="footer-link">Terms of Service</a>
            <a href="#" class="footer-link">Contact</a>
        </div>
    </div>
</footer>

<script>
document.getElementById('year').textContent = new Date().getFullYear();
</script>

<style>
.footer {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
    color: #a0a0a0;
    padding: 0;
    position: fixed;
    bottom: 0;
    width: 100%;
    border-top: 1px solid rgba(0, 212, 255, 0.1);
    z-index: 998;
    box-shadow: 0 -8px 24px rgba(0, 0, 0, 0.3);
}

.footer-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 30px;
    max-width: 1400px;
    margin: 0 auto;
    width: 100%;
}

.footer-left p {
    margin: 0;
    font-size: 13px;
    font-weight: 500;
}

.footer-right {
    display: flex;
    gap: 20px;
}

.footer-link {
    color: #a0a0a0;
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    transition: all 0.3s ease;
    position: relative;
}

.footer-link::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 0;
    height: 2px;
    background: #00d4ff;
    transition: width 0.3s ease;
}

.footer-link:hover {
    color: #00d4ff;
}

.footer-link:hover::after {
    width: 100%;
}

@media (max-width: 768px) {
    .footer-content {
        flex-direction: column;
        gap: 10px;
        padding: 12px 15px;
    }
    
    .footer-left p {
        font-size: 12px;
    }
    
    .footer-right {
        gap: 12px;
    }
    
    .footer-link {
        font-size: 12px;
    }
}
</style>