(function ($) {
    "use strict";

    function productCard(item) {
        return (
            '<div class="col-md-6 col-lg-4 col-xl-3">' +
                '<div class="rounded position-relative fruite-item">' +
                    '<div class="fruite-img">' +
                        '<a href="' + item.url + '"><img src="' + item.image + '" class="img-fluid w-100 rounded-top" alt=""></a>' +
                    '</div>' +
                    '<div class="text-white bg-secondary px-3 py-1 rounded position-absolute" style="top: 10px; left: 10px;">' + (item.badge || "Fresh") + "</div>" +
                    '<div class="p-4 border border-secondary border-top-0 rounded-bottom">' +
                        "<h4><a href=\"" + item.url + "\" class=\"text-dark\">" + item.name + "</a></h4>" +
                        "<p>" + (item.description || "Fresh organic product from our store.") + "</p>" +
                        '<div class="d-flex justify-content-between flex-lg-wrap">' +
                            '<p class="text-dark fs-5 fw-bold mb-0">$' + Number(item.price).toFixed(2) + "</p>" +
                            '<a href="' + item.url + '" class="btn border border-secondary rounded-pill px-3 text-primary"><i class="fa fa-shopping-bag me-2 text-primary"></i> Add to cart</a>' +
                        "</div>" +
                    "</div>" +
                "</div>" +
            "</div>"
        );
    }

    function renderContact(root, data) {
        root.innerHTML =
            '<div class="row g-5">' +
                '<div class="col-lg-6">' +
                    "<h2 class=\"mb-4\">Get in touch</h2>" +
                    "<p>Have a question for " + data.store + "? Send us a message and we will get back to you.</p>" +
                    "<p><i class=\"fas fa-map-marker-alt me-2 text-secondary\"></i> Fresh products storefront</p>" +
                "</div>" +
                '<div class="col-lg-6">' +
                    '<form class="row g-4">' +
                        '<div class="col-12"><input class="form-control p-3" placeholder="Your Name"></div>' +
                        '<div class="col-12"><input class="form-control p-3" type="email" placeholder="Your Email"></div>' +
                        '<div class="col-12"><textarea class="form-control p-3" rows="5" placeholder="Your Message"></textarea></div>' +
                        '<div class="col-12"><button class="btn btn-primary border-secondary rounded-pill px-4 py-3 text-white" type="button">Submit</button></div>' +
                    "</form>" +
                "</div>" +
            "</div>";
    }

    function renderCart(root, data) {
        var rows = (data.products || []).slice(0, 4).map(function (item) {
            return (
                "<tr>" +
                    '<td><img src="' + item.image + '" width="70" class="rounded me-2"> ' + item.name + "</td>" +
                    "<td>$" + Number(item.price).toFixed(2) + "</td>" +
                    "<td>1</td>" +
                    "<td>$" + Number(item.price).toFixed(2) + "</td>" +
                "</tr>"
            );
        }).join("");
        root.innerHTML =
            "<h2 class=\"mb-4\">Shop Cart</h2>" +
            '<div class="table-responsive">' +
                '<table class="table">' +
                    "<thead><tr><th>Products</th><th>Price</th><th>Quantity</th><th>Total</th></tr></thead>" +
                    "<tbody>" + (rows || '<tr><td colspan="4">Your cart is empty.</td></tr>') + "</tbody>" +
                "</table>" +
            "</div>" +
            '<a class="btn btn-primary border-secondary rounded-pill px-4 py-2 text-white" href="' + data.urls.shop + '">Continue shopping</a>';
    }

    function renderProducts(root, data) {
        var cards = (data.products || []).map(productCard).join("");
        root.innerHTML =
            "<h2 class=\"mb-4\">" + (data.page === "shop" ? "Shop" : "Products") + "</h2>" +
            '<div class="row g-4">' + (cards || "<p>No products available.</p>") + "</div>";
    }

    var pages = {
        contact: renderContact,
        cart: renderCart,
        checkout: renderCart,
        shop: renderProducts,
        home: renderProducts
    };

    $(function () {
        $(".btn-plus, .btn-minus").on("click", function () {
            var input = $(this).closest(".quantity").find("input");
            var value = parseInt(input.val(), 10) || 1;
            input.val($(this).hasClass("btn-plus") ? value + 1 : Math.max(1, value - 1));
        });

        var root = document.getElementById("theme-dynamic-page");
        var data = window.FRUITABLES || {};
        if (root && pages[data.page]) {
            pages[data.page](root, data);
        }
    });
})(jQuery);
