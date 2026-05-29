<?php
require_once 'config/config.php';
require_once 'includes/header.php';
?>

<div class="container py-5 mt-4" style="min-height: 65vh;">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h1 class="fw-bold text-center mb-3">Customer Support</h1>
            <p class="text-muted text-center mb-5">Have a question or need assistance? We're here to help! Fill out the form below and our team will get back to you within 24 hours.</p>
            
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5">
                    <form action="#" method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold" for="name">Full Name</label>
                                <input type="text" id="name" name="name" class="form-control" required placeholder="John Doe">
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-bold" for="email">Email Address</label>
                                <input type="email" id="email" name="email" class="form-control" required placeholder="john@example.com">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold" for="subject">Subject</label>
                            <select class="form-select" id="subject" name="subject" required>
                                <option value="" disabled selected>Select an issue</option>
                                <option value="order">Order Inquiry</option>
                                <option value="return">Returns & Refunds</option>
                                <option value="seller">Seller Question</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold" for="message">Message</label>
                            <textarea id="message" name="message" class="form-control" rows="5" required placeholder="How can we help you today?"></textarea>
                        </div>

                        <button type="button" class="btn btn-primary-custom btn-lg w-100" onclick="alert('Support ticket submitted successfully! (Mock)')">
                            <i class="fas fa-paper-plane me-2"></i> Send Message
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
