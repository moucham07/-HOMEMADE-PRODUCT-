<?php
require_once 'config/config.php';
require_once 'includes/header.php';
?>

<div class="container py-5 mt-4" style="min-height: 60vh;">
    <h1 class="fw-bold text-center mb-5">Frequently Asked Questions</h1>
    
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="accordion" id="faqAccordion">
                
                <!-- FAQ Item 1 -->
                <div class="accordion-item border-0 shadow-sm mb-3 rounded">
                    <h2 class="accordion-header" id="headingOne">
                        <button class="accordion-button fw-bold" type="button" data-mdb-collapse-init data-mdb-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                            How do I know the products are truly handmade?
                        </button>
                    </h2>
                    <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-mdb-parent="#faqAccordion">
                        <div class="accordion-body text-muted">
                            Every artisan on our platform goes through a strict verification process. We ensure that all products sold on ArtisanHaven are handcrafted, customized, or made in small batches by independent creators.
                        </div>
                    </div>
                </div>

                <!-- FAQ Item 2 -->
                <div class="accordion-item border-0 shadow-sm mb-3 rounded">
                    <h2 class="accordion-header" id="headingTwo">
                        <button class="accordion-button collapsed fw-bold" type="button" data-mdb-collapse-init data-mdb-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                            What is your return policy?
                        </button>
                    </h2>
                    <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-mdb-parent="#faqAccordion">
                        <div class="accordion-body text-muted">
                            Because most items are custom or handmade to order, return policies vary by seller. Generally, you have 14 days to request a return for non-customized items. Please check the specific seller's shop policies for exact details.
                        </div>
                    </div>
                </div>

                <!-- FAQ Item 3 -->
                <div class="accordion-item border-0 shadow-sm mb-3 rounded">
                    <h2 class="accordion-header" id="headingThree">
                        <button class="accordion-button collapsed fw-bold" type="button" data-mdb-collapse-init data-mdb-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                            How can I track my order?
                        </button>
                    </h2>
                    <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-mdb-parent="#faqAccordion">
                        <div class="accordion-body text-muted">
                            Once your order ships, you will receive a tracking number via email. You can also track your order directly on our website by visiting the <a href="track_order.php">Track Order</a> page and entering your Order ID.
                        </div>
                    </div>
                </div>

                <!-- FAQ Item 4 -->
                <div class="accordion-item border-0 shadow-sm mb-3 rounded">
                    <h2 class="accordion-header" id="headingFour">
                        <button class="accordion-button collapsed fw-bold" type="button" data-mdb-collapse-init data-mdb-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                            How do I become a seller?
                        </button>
                    </h2>
                    <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-mdb-parent="#faqAccordion">
                        <div class="accordion-body text-muted">
                            We are always looking for talented artisans! Simply click the "Become a Seller" link in the footer, register for an account, and set up your storefront. It's free to join!
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
