<footer class="footer animate-fade-in">
    <div class="footer-container">
        <div class="footer-left">
            <p>&copy; <?= date('Y') ?> <span class="accent-text">Backbenchers</span> Inventory System. Built for Performance.</p>
        </div>
        <div class="footer-right">
            <ul class="footer-links">
                <li><a href="#">Support</a></li>
                <li><a href="#">Privacy</a></li>
                <li><a href="#">Terms</a></li>
            </ul>
        </div>
    </div>
</footer>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    
    // Success Alerts
    if (urlParams.has('success')) {
        Swal.fire({
            icon: 'success',
            title: 'Operation Successful',
            text: 'The record has been updated successfully.',
            timer: 2000,
            showConfirmButton: false
        });
    }
    
    // Deletion Alert
    if (urlParams.has('deleted')) {
        Swal.fire({
            icon: 'success',
            title: 'Record Archived',
            text: 'The item has been removed from active inventory.',
            timer: 2000,
            showConfirmButton: false
        });
    }

    // Error Alert
    if (urlParams.has('error')) {
        let errorMsg = 'An error occurred while processing your request.';
        let errorTitle = 'Operation Failed';
        
        const errorType = urlParams.get('error');
        if (errorType === 'is_sold') {
            errorTitle = 'Restricted Deletion';
            errorMsg = 'This item has sales records and cannot be deleted. Archive it instead.';
        } else if (errorType === 'not_found') {
            errorMsg = 'The requested item could not be found.';
        }

        Swal.fire({
            icon: 'error',
            title: errorTitle,
            text: errorMsg,
            confirmButtonColor: 'var(--accent-primary)'
        });
    }

    // Clear URL parameters without reloading
    if (urlParams.has('success') || urlParams.has('deleted') || urlParams.has('error')) {
        window.history.replaceState({}, document.title, window.location.pathname);
    }
});
</script>

<style>
.footer {
    width: 100%;
    padding: 1.5rem 2rem;
    margin-top: auto;
    border-top: 1px solid var(--border-color);
    background: var(--bg-surface);
}

.footer-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    max-width: 1600px;
    margin: 0 auto;
    width: 100%;
}

.footer-left p {
    font-size: 0.85rem;
    color: var(--text-muted);
}

.accent-text {
    color: var(--accent-primary);
    font-weight: 700;
}

.footer-links {
    list-style: none;
    display: flex;
    gap: 1.5rem;
}

.footer-links a {
    text-decoration: none;
    font-size: 0.85rem;
    color: var(--text-muted);
    transition: var(--transition-fast);
}

.footer-links a:hover {
    color: var(--accent-primary);
}

@media (max-width: 768px) {
    .footer-container {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
}
</style>