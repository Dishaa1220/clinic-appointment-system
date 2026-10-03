<?php
// includes/footer.php
// Closes the main container, adds the footer text, Bootstrap JS and the delete-confirmation script.
?>
</div><!-- end container -->

<footer class="text-center text-muted py-3 border-top bg-white">
    <small>Clinic Appointment System &copy; <?php echo date("Y"); ?></small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Any link with class "confirm-delete" asks for confirmation first.
document.querySelectorAll('.confirm-delete').forEach(function (link) {
    link.addEventListener('click', function (event) {
        var message = link.getAttribute('data-message') || 'Are you sure you want to delete this record?';
        if (!confirm(message)) {
            event.preventDefault();
        }
    });
});
</script>
</body>
</html>