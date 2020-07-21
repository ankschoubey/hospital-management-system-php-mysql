    <div class="container footer">
      <hr>
      <footer>
        <p align="right">
        <?php
                if (!isset($_SESSION['username'])) {
                    echo '<a class="nav-link" href="hms-staff.php">Staff Login</a>';
                }
        ?>
        </p>
        <p class="text-center">
        Originally Made using HMS by KAP - Copyright &copy; <?php echo date('Y'); ?> Tshaba Phomolo Benedict
		</p>
      </footer>
    </div>
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/custom.js"></script>
  </body>
</html>
