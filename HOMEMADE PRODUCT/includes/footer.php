<!-- Footer -->
<footer class="footer text-center text-lg-start">
  <!-- Section: Links  -->
  <section class="">
    <div class="container text-center text-md-start mt-5">
      <!-- Grid row -->
      <div class="row mt-3">
        <!-- Grid column -->
        <div class="col-md-3 col-lg-4 col-xl-3 mx-auto mb-4">
          <!-- Content -->
          <h6 class="text-uppercase fw-bold mb-4" style="color: var(--primary-color);">
            <i class="fas fa-gem me-3"></i>ArtisanHaven
          </h6>
          <p>
            Your premium marketplace for authentic, handcrafted products from independent artisans and creators around the world.
          </p>
        </div>
        <!-- Grid column -->

        <!-- Grid column -->
        <div class="col-md-2 col-lg-2 col-xl-2 mx-auto mb-4">
          <!-- Links -->
          <h6 class="text-uppercase fw-bold mb-4">Categories</h6>
          <?php
          $footer_cats = [];
          if (isset($conn)) {
              $stmt_f = $conn->query("SELECT id, name FROM categories LIMIT 4");
              $footer_cats = $stmt_f->fetchAll();
          }
          if (empty($footer_cats)):
          ?>
              <p><a href="/HOMEMADE PRODUCT/products.php" class="footer-link text-decoration-none">Handmade Jewelry</a></p>
              <p><a href="/HOMEMADE PRODUCT/products.php" class="footer-link text-decoration-none">Home Decor</a></p>
              <p><a href="/HOMEMADE PRODUCT/products.php" class="footer-link text-decoration-none">Paintings</a></p>
              <p><a href="/HOMEMADE PRODUCT/products.php" class="footer-link text-decoration-none">Custom Gifts</a></p>
          <?php else: ?>
              <?php foreach($footer_cats as $fc): ?>
                  <p><a href="/HOMEMADE PRODUCT/products.php?category=<?php echo $fc['id']; ?>" class="footer-link text-decoration-none"><?php echo htmlspecialchars($fc['name']); ?></a></p>
              <?php endforeach; ?>
          <?php endif; ?>
        </div>
        <!-- Grid column -->

        <!-- Grid column -->
        <div class="col-md-3 col-lg-2 col-xl-2 mx-auto mb-4">
          <!-- Links -->
          <h6 class="text-uppercase fw-bold mb-4">Useful links</h6>
          <p><a href="/HOMEMADE PRODUCT/register.php?role=seller" class="footer-link text-decoration-none">Become a Seller</a></p>
          <p><a href="/HOMEMADE PRODUCT/faq.php" class="footer-link text-decoration-none">FAQ</a></p>
          <p><a href="/HOMEMADE PRODUCT/track_order.php" class="footer-link text-decoration-none">Track Order</a></p>
          <p><a href="/HOMEMADE PRODUCT/support.php" class="footer-link text-decoration-none">Support</a></p>
        </div>
        <!-- Grid column -->

        <!-- Grid column -->
        <div class="col-md-4 col-lg-3 col-xl-3 mx-auto mb-md-0 mb-4">
          <!-- Links -->
          <h6 class="text-uppercase fw-bold mb-4">Contact</h6>
          <p><i class="fas fa-home me-3 footer-link"></i> Pathsala 781325, Assam</p>
          <p><i class="fas fa-envelope me-3 footer-link"></i> info@artisanhaven.com</p>
          <p><i class="fas fa-phone me-3 footer-link"></i> + 01 234 567 88</p>
        </div>
        <!-- Grid column -->
      </div>
      <!-- Grid row -->
    </div>
  </section>
  <!-- Section: Links  -->

  <!-- Copyright -->
  <div class="text-center p-4" style="background-color: rgba(0, 0, 0, 0.2);">
    © 2026 Copyright:
    <a class="text-reset fw-bold" href="#">ArtisanHaven.com</a>
  </div>
  <!-- Copyright -->
</footer>
<!-- Footer -->

<!-- MDBootstrap JS -->
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.umd.min.js"></script>
<!-- Custom JS -->
<script src="/HOMEMADE PRODUCT/assets/js/main.js"></script>
</body>
</html>
