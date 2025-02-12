"use strict";

/* POS - Point of Sale system starts */
if (document.readyState == "loading") {
  document.addEventListener("DOMContentLoaded", ready);
} else {
  ready();
}

function ready() {
  display_cart();
  var addToCartButtons = document.getElementsByClassName("shop-item-button");
  for (var i = 0; i < addToCartButtons.length; i++) {
    var button = addToCartButtons[i];
    button.addEventListener("click", add_to_cart);
  }
}

function purchaseClicked() {
  var cartItems = document.getElementsByClassName("cart-items")[0];
  while (cartItems.hasChildNodes()) {
    cartItems.removeChild(cartItems.firstChild);
  }
  update_cart_total();
}
$(document).on("click", ".remove-cart-item", function (e) {
  e.preventDefault();
  iziToast.error({
    message: ["Product removed from cart"]
  });
  var variant_id = $(this).data("variant_id");
  $(this).parent().parent().remove();
  var cart = localStorage.getItem("cart");
  cart = localStorage.getItem("cart") !== null ? JSON.parse(cart) : null;
  if (cart) {
    var new_cart = cart.filter(function (item) {
      return item.variant_id != variant_id;
    });
    localStorage.setItem("cart", JSON.stringify(new_cart));
    display_cart();
  }
});

$(document).on("click", ".cart-quantity-input", function (e) {
  var operation = $(this).data("operation");
  var variant_id = $(this).siblings().val();
  var input =
    operation == "plus" ? $(this).siblings()[1] : $(this).siblings()[2];
  var qty = $(this)
    .parent()
    .siblings(".item-quantity")
    .find(".cart-quantity-input")
    .val();
  var qty = parseInt(input.value, 10);
  var data = (input.value = operation == "minus" ? qty - 1 : qty + 1);
  update_quantity(data, variant_id);
});

$(document).on("change", ".cart-quantity-input-new", function (e) {
  var variant_id = $(this).siblings().val();
  var quantity = $(this).val();
  var data = quantity;

  update_quantity(data, variant_id);
});

function update_quantity(data, variant_id) {
  if (isNaN(data) || data <= 0) {
    data = 1;
  }
  var cart = localStorage.getItem("cart");
  cart = localStorage.getItem("cart") !== null ? JSON.parse(cart) : null;
  if (cart) {
    var i = cart.map((i) => i.variant_id).indexOf(variant_id);
    cart[i].quantity = data;
    localStorage.setItem("cart", JSON.stringify(cart));
    display_cart();
  }
}

function SafeParseFloat(val) {
  if (isNaN(val)) {
    if ((val = val.match(/([0-9\.,]+\d)/g))) {
      val = val[0].replace(/[^\d\.]+/g, "");
    }
  }
  return parseFloat(val);
}

function display_cart() {
  var cart = localStorage.getItem("cart");

  cart = localStorage.getItem("cart") !== null ? JSON.parse(cart) : null;

  var currency = $(".cart-total-price").attr("data-currency");
  var cartRowContents = "";
  var display_add_ons = "";
  if (cart !== null && cart.length > 0) {
    cart.forEach((item) => {
      cartRowContents += `<div class="container">
        <div class="row">
                    <div class="col-md-6 d-flex">
                        <div class="cart-image">
                            <img class="mr-0 rounded" src="${item.image}">
                        </div>
                        <div class="">
                        <p class="cart-item-title ml-2 mb-2">${item.title}</p>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <span class="cart-price">${currency +
        parseFloat(item.display_price).toLocaleString()
        }</span>
                    </div>
                    <div class="col-md-2">
                    <div class="input-group-prepend">
                        <input type="hidden" class="product-variant" name="variant_ids[]" type="number" value=${item.variant_id
        }>
                        <button type="button" class="cart-quantity-input btn btn-xs btn-secondary" data-operation="plus">+</button>
                            <input class="cart-quantity-input-new form-control text-center p-0" name="quantity[]"value="${item.quantity
        }">
                        <button type="button" class="cart-quantity-input btn btn-xs btn-secondary" data-operation="minus">-</button>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-xs btn-danger remove-cart-item"  data-variant_id=${item.variant_id
        }><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            </div><div></div>`;
      var add_ons_items = item.add_ons;

      if (add_ons_items.length > 0) {
        cartRowContents += `<input type="hidden" class="add_ons_calories" name="add_ons_calories[]" value=${item.add_ons_calories}>
        <input type="hidden" class="add_ons_date_created" name="add_ons_date_created[]" value=${item.add_ons_date_created}>
        <input type="hidden" class="add_ons_description" name="add_ons_description[]" value=${item.add_ons_description}>
        <input type="hidden" class="add_ons_status" name="add_ons_status[]" value=${item.add_ons_status}>
        <input type="hidden" class="add_ons_title" name="add_ons_title[]" value=${item.add_ons_title}>
        <input type="hidden" class="display_price" name="display_price[]" value=${item.display_price}>
        <input type="hidden" class="add_ons_id" name="add_ons_id[]" value=${item.add_ons_id}>
        <small class="d-block"><strong><u>Addons: </u></strong>`;
        for (var j = 0; j < add_ons_items.length; j++) {
          cartRowContents +=
            `<div class="font-size-sm text-body">
                                    <span>` +
            add_ons_items[j] +
            `</span>
                                    <span class="font-weight-bold"></span>
                                </div>`;
        }
        cartRowContents += `</small><br>`;
      }
    });
  } else {
    cartRowContents += `
        <div class="container">
            <div class="row">
                <div class="col mt-4 d-flex justify-content-center text-primary h5">No items in cart</div>
            </div>
        </div>`;
  }
  $(".cart-items").html(cartRowContents);
  update_cart_total();
}

function get_cart_total() {
  var cart = localStorage.getItem("cart");
  var cart = cart !== null && cart !== undefined ? JSON.parse(cart) : null;

  var cart_total = 0;
  if (cart !== null && cart !== undefined) {
    cart_total = cart.reduce(
      (cart_total, item) =>
        cart_total + parseFloat(item.display_price) * parseFloat(item.quantity),
      0
    );
  }
  var currency = $("#cart-total-price").attr("data-currency");
  var total = {
    currency: currency,
    cart_total: cart_total,
    cart_total_formated: parseFloat(cart_total).toLocaleString()
  };
  return total;
}

function update_cart_total() {
  var cart = localStorage.getItem("cart");
  var cart = cart !== null && cart !== undefined ? JSON.parse(cart) : null;
  var sum = 0;
  var test = [];
  var qty = 0;
  if (cart !== null && cart.length > 0) {
    cart.forEach((item) => {
      test = item.add_ons_price;
      qty = item.quantity;
      for (let i = 0; i < test.length; i++) {
        sum += test[i] << 0;
      }
    });
  }
 
  var total = get_cart_total();
  const add_ons = parseFloat(sum) * parseFloat(qty);
  const add_ons_total =
    parseFloat(total.cart_total_formated) + parseFloat(add_ons);
  $("#cart-total-price").html(total.currency + "" + add_ons_total);
  return;
}

// get products
function get_products(
  category_id = "",
  limit = 2,
  offset = 0,
  search_parameter = ""
) {
  $.ajax({
    type: "GET",
    url: `${base_url}partner/point_of_sale/get_products?category_id=${category_id}&limit=${limit}&offset=${offset}&search=${search_parameter}`,
    dataType: "json",
    beforeSend: function () {
      $("#get_products").html(
        `<div class="text-center" style='min-height:450px;' ><h4>Please wait.. . loading products..</h4></div>`
      );
    },
    success: function (data) {
      if (data.error == false) {
        $("#total_products").val(data.products.total);
        $("#get_products").empty();
        display_products(data.products);
        var total = $("#total_products").val();
        var current_page = $("#current_page").val();
        var limit = $("#limit").val();
        var search_parameter = $("#search_products").val();
        paginate(total, current_page, limit, search_parameter);
      } else {
        $("#get_products").html(data.message);
        $("#get_products").empty();
      }
    }
  });
}

var add_ons_items = [];
$(document).on("click", ".modal-trigger", function () {
  add_ons_items = [];
  var id = "";
  id = $(this).data("id");
  $("#product_id").val(id);

  $.ajax({
    type: "GET",
    url: `${base_url}partner/point_of_sale/add_ons_list?product_id=${id}`,
    dataType: "json",

    success: function (data) {
      $("#add_ons_check").empty();
      $("#addon").empty();

      add_ons_items = [];
      var currency = $(".cart-total-price").attr("data-currency");
      var products = "";
      var i;
      if (data.error == false) {
        var product_add_ons = data.add_ons;

        document.getElementById("addon").innerHTML =
          product_add_ons.length > 0 ? "AddOn" : "";

        for (i = 0; i < product_add_ons.length; i++) {
          var add_ons = "";
          var add_ons_price = "";
          var add_ons_id = "";
          var calories = "";
          var date_created = "";
          var description = "";
          var status = "";

          add_ons = `${product_add_ons[i].title}`;
          add_ons_price = `${product_add_ons[i].price}`;
          add_ons_id = `${product_add_ons[i].id}`;
          calories = `${product_add_ons[i].calories}`;
          date_created = `${product_add_ons[i].date_created}`;
          description = `${product_add_ons[i].description}`;
          status = `${product_add_ons[i].status}`;

          document.getElementById("add_ons_check").innerHTML +=
            '<div class="form-check p-1" id="add_on">' +
            '<input class="btn-check addon-chek" type="checkbox" id="' +
            add_ons_id +
            '" onchange="" name="add_on" data-price="' +
            add_ons_price +
            '" data-calories="' +
            calories +
            '" data-date_created="' +
            date_created +
            '" data-description="' +
            description +
            '" data-status="' +
            status +
            '" data-add_ons_id="' +
            add_ons_id +
            '" data-title="' +
            add_ons +
            '" value = "' +
            add_ons +
            "     " +
            currency +
            add_ons_price +
            '" autocomplete="off">' +
            '<label class="d-flex align-items-center btn btn-sm check-label addon-input mb-0 h-100 break-all" for="' +
            add_ons_id +
            '">' +
            add_ons +
            "<br>" +
            currency +
            add_ons_price +
            "</label></div>";
        }
      }
    }
  });
});

var cart_item;
function add_items(e) {
  $("#product_detail").empty();

  var button = e.target;
  var shopItem = button.parentElement.parentElement;

  var variant_dropdown = shopItem.children[0].children[4];

  var display_price = variant_dropdown.value;
  var product_id = shopItem.getElementsByClassName("shop-item-id")[0].innerText;
  var variant_id =
    variant_dropdown.options[variant_dropdown.selectedIndex].dataset.variant_id;
  var partner_id = shopItem.getElementsByClassName("shop-item-partner-id")[0]
    .innerText;
  var variant_values =
    variant_dropdown.options[variant_dropdown.selectedIndex].dataset
      .variant_values;
  var special_price =
    variant_dropdown.options[variant_dropdown.selectedIndex].dataset
      .special_price;
  var price =
    variant_dropdown.options[variant_dropdown.selectedIndex].dataset.price;
  var title = shopItem.getElementsByClassName("shop-item-title")[0].innerText;

  var image = shopItem.getElementsByClassName("item-image")[0].src;
  var currency = $(".cart-total-price").attr("data-currency");

  document.getElementById("product_detail").innerHTML =
    '<div class="d-flex align-items-center justify-content-center active">' +
    '<img class="img-responsive rounded" width="160" src="' +
    image +
    '" data-zoom="" alt="Product image">' +
    '<div class="cz-image-zoom-pane"></div>' +
    "</div>" +
    '<div class="details p-4">' +
    '<div class="break-all">' +
    '<p class="d-block mb-2 product-title text-sm-right">' +
    title +
    "</p>" +
    "</div>" +
    '<div class="mb-2 text-dark d-flex align-items-baseline gap-2">' +
    '<h3 class="font-weight-normal text-accent mb-0 text-end">' +
    currency +
    display_price +
    "</h3>" +
    "</div>" +
    "</div>";
  //   cart_item = [];
  cart_item = {
    product_id: product_id.trim(),
    partner_id: partner_id.trim(),
    variant_id: variant_id,
    title: title,
    variant: variant_values,
    image: image,
    display_price: display_price.trim(),
    quantity: 1,
    special_price: special_price,
    price: price
  };



  add_ons_items = [];
  $("#add_ons_check").empty();

}
$("#add_add_ons_button").on("click", function () {
  add_to_cart(cart_item);
});

function add_to_cart(cart_item) {
  
  var display_price = cart_item.display_price;
  var product_id = cart_item.product_id;
  var partner_id = cart_item.partner_id;
  var variant_id = cart_item.variant_id;
  var title = cart_item.title;
  var variant = cart_item.variant;
  var image = cart_item.image;
  var display_price = cart_item.display_price;
  var quantity = 1;
  var special_price = cart_item.special_price;
  var price = cart_item.price;

  var grid = document.getElementById("add_ons_check");
  var checkBoxes = grid.getElementsByTagName("INPUT");
  var add_ons = [];
  var add_ons_price = [];
  var add_ons_calories = [];
  var add_ons_date_created = [];
  var add_ons_description = [];
  var add_ons_status = [];
  var add_ons_title = [];
  var add_ons_id = [];
  for (var i = 0; i < checkBoxes.length; i++) {
    if (checkBoxes[i].checked) {
      add_ons.push(checkBoxes[i].value);
      add_ons_price.push(checkBoxes[i].dataset.price);
      add_ons_calories.push(checkBoxes[i].dataset.calories);
      add_ons_date_created.push(checkBoxes[i].dataset.date_created);
      add_ons_description.push(checkBoxes[i].dataset.description);
      add_ons_status.push(checkBoxes[i].dataset.status);
      add_ons_title.push(checkBoxes[i].dataset.title);
      add_ons_id.push(checkBoxes[i].dataset.add_ons_id);

      
    }
  }
  var items = {
    product_id: product_id.trim(),
    partner_id: partner_id.trim(),
    variant_id: variant_id,
    title: title,
    variant: variant,
    image: image,
    display_price: display_price.trim(),
    quantity: 1,
    special_price: special_price,
    price: price,
    add_ons: add_ons,
    add_ons_price: add_ons_price,
    add_ons_calories: add_ons_calories,
    add_ons_date_created: add_ons_date_created,
    add_ons_description: add_ons_description,
    add_ons_status: add_ons_status,
    add_ons_title: add_ons_title,
    add_ons_id: add_ons_id
  };

  var cart = localStorage.getItem("cart");

  cart = localStorage.getItem("cart") !== null ? JSON.parse(cart) : null;

  if (cart !== null && cart !== undefined) {
    if (cart.find((item) => item.variant_id === variant_id)) {
      alert("This item is already present in your cart");
      return;
    } else {
      iziToast.success({
        message: ["Product added to cart"]
      });
    }
    cart.push(items);
  } else {
    cart = [items];
  }

  localStorage.setItem("cart", JSON.stringify(cart));

  display_cart();
}

// display products

var prod_id = "";
function display_products(products = "") {
  var display_products = "";
  var i;
  var j;
  var k;
  var product_id;
  var products = products.product;

  if (products !== null && products.length > 0) {
    for (i = 0; i < products.length; i++) {
      display_products +=
        '<div class="text-center col-lg-4">' +
        '<div class="shop-item m-3">' +
        '<span class="d-none shop-item-id">' +
        " <b>" +
        products[i].id +
        "</b>" +
        " </span>" +
        '<span class="shop-item-title ">' +
        " <a href=" +
        base_url +
        "partner/product/view_product?edit_id=" +
        products[i].id +
        ' target="_BLANK"><b class="text text-dark">' +
        products[i].name +
        "</b></a>" +
        " </span>" +
        '<div class="shop-item-image d-flex justify-content-center align-item-center">' +
        '  <img class="item-image" src="' +
        products[i].image +
        '" />' +
        "</div>" +
        '<span class="d-none shop-item-partner-id">' +
        products[i].partner_id +
        "</span>" +
        '<select class="form-control mt-4 product-variants variant_value" id="change">';

      var total_price = document.getElementById("cart-total-price");
      prod_id = products[i].partner_id;
      var currency = "";
      if ($("#cart-total-price").length) {
        currency = total_price.getAttribute("data-currency");
      }
      var variants = products[i]["variants"];
      for (j = 0; j < variants.length; j++) {
        var variant_values = variants[j]["variant_values"]
          ? variants[j]["variant_values"] + " - "
          : "";

        var variant_price =
          variants[j]["special_price"] > 0
            ? variants[j]["special_price"]
            : variants[j]["price"];
        display_products +=
          '<option data-variant_values="' +
          variants[j]["variant_values"] +
          '" data-price="' +
          variants[j]["price"] +
          '" data-special_price="' +
          variants[j]["special_price"] +
          '" data-variant_id="' +
          variants[j]["id"] +
          '" value="' +
          variant_price +
          " " +
          '" class="shop-item-price" > ' +
          variant_values +
          currency +
          " " +
          parseFloat(variant_price).toLocaleString() +
          "</option > ";
      }
      display_products +=
        "</select></div>" +
        ' <div class ="shop-item-details justify-content-center">' +
        // ' <button class ="btn btn-xs btn-info shop-item-button p-2" onclick="add_to_cart(event)" type ="button">Add to Cart</button>' +
        '<button class ="btn btn-xs btn-info shop-item-button p-2 modal-trigger" data-toggle="modal" data-id="' +
        products[i].id +
        '"onclick="add_items(event)" type ="button" data-target="#modal2">Add to Cart</button>' +
        "</div>";

      display_products += "</div>" + "</div>" + "</div>";
    }

    $("#get_products").append(display_products);
  } else {
    $("#get_products").html(
      `<div class="text-center" style='min-height:450px;' ><h4> No products available in this category...</h4></div>`
    );
  }
}

var session_user_id = $("#session_user_id").val();
var cart = localStorage.getItem("cart");
cart = cart != null || cart == "" ? JSON.parse(cart) : "";
if (cart != "") {
  if (cart.find((item) => item.partner_id != session_user_id)) {
    localStorage.removeItem("cart");
    display_cart();
  }
}


$(document).ready(function () {
  var category_id = $("#product_categories").val();
  var limit = $("#limit").val();
  var offset = $("#offset").val();
  get_products(category_id, limit, offset);
});

// category wise product change
$("#product_categories").on("change", function () {
  var category_id = $("#product_categories").val();
  var limit = $("#limit").val();
  $("#current_page").val("0");
  get_products(category_id, limit, 0);
});

$(document).ready(function () {
  $("#product_categories").on("change", function () {
    $("#get_products").empty();
  });
});

// transaction id input
$(document).ready(function () {
  $(".transaction_id").hide();
  $(".payment_method_name").hide();
});

/* payment method selected event  */
$(".payment_method").on("click", function () {
  var payment_method = $(this).val();
  var exclude_txn_id = ["COD"];
  var include_payment_method_name = ["other"];

  if (exclude_txn_id.includes(payment_method)) {
    $(".transaction_id").hide();
  } else {
    $(".transaction_id").show();
  }

  if (include_payment_method_name.includes(payment_method)) {
    $(".payment_method_name").show();
  } else {
    $(".payment_method_name").hide();
  }
});

// select 2 js select user
$(".select_user").select2({
  ajax: {
    url: base_url + "partner/point_of_sale/get_users",
    type: "GET",
    dataType: "json",
    delay: 250,
    data: function (params) {
      return {
        search: params.term // search term
      };
    },
    processResults: function (response) {
      return {
        results: response
      };
    },
    cache: true
  },
  minimumInputLength: 1,
  theme: "bootstrap4",
  placeholder: "Search for user"
});
// clear selected values in select2

$("#clear_user_search").on("click", function () {
  $(".select_user").empty();
});

// Register in pos

$(document).on("submit", "#register_form", function (e) {
  e.preventDefault();
  var name = $("#name").val();
  var mobile = $("#mobile").val();
  var formData = new FormData(this);
  formData.append(csrfName, csrfHash);
  $.ajax({
    type: "POST",
    url: $(this).attr("action"),
    dataType: "json",
    data: formData,
    processData: false,
    contentType: false,

    beforeSend: function () {
      $("#save-register-result-btn").html("Please Wait..");
      $("#save-register-result-btn").attr("disabled", true);
    },
    success: function (result) {
      csrfName = result["csrfName"];
      csrfHash = result["csrfHash"];
      if (result.error == false) {
        iziToast.success({
          message: result.message
        });
        $("#register_form")[0].reset();
      } else {
        iziToast.error({
          message: result.message
        });
      }
      $("#save-register-result-btn").html("Register").attr("disabled", false);
    }
  });
});

var pos_user_id = 0;
$("#select_user_id").on("change", function () {
  pos_user_id = $("#select_user_id").val();
});

$("#pos_form").on("submit", function (e) {
  e.preventDefault();
  if (confirm("Are you sure? want to check out.")) {
    var cart = localStorage.getItem("cart");
    if (cart == null || !cart) {
      var message = "Please add items to cart";
      show_message("Oops!", message, "error");
      return;
    }
    var payment_method = $(".payment_method:checked").val();
    var self_pickup =
      $(".self_pickup:checked").length > 0
        ? $(".self_pickup:checked").val()
        : 0;

    if (!payment_method) {
      var message = "Please choose a payment method";
      show_message("Oops!", message, "error");
      return;
    }
    var txn_id = $("#transaction_id").val();
    if (!txn_id && payment_method != "COD") {
      var message = "Please enter  transaction id";
      show_message("Oops!", message, "error");
      return;
    }
    var payment_method_name = $("#payment_method_name").val();
    if (!payment_method_name) {
      payment_method_name = "";
    }
    const request_body = {
      [csrfName]: csrfHash,
      data: cart,
      payment_method: payment_method,
      self_pickup: self_pickup,
      user_id: pos_user_id,
      txn_id: txn_id,
      payment_method_name: payment_method_name
    };
    $.ajax({
      type: "POST",
      url: $(this).attr("action"),
      data: request_body,
      dataType: "json",
      success: function (result) {
        csrfName = result["csrfName"];
        csrfHash = result["csrfHash"];
        if (result.error == true) {
          iziToast.error({
            message: "<span>" + result.message + "</span> "
          });
        } else {
          iziToast.success({
            message:
              '<span class="messages_style">' +
              result.message +
              "</span> "
          });
          delete_cart_items();
          setTimeout(function () {
            location.reload();
          }, 600);
        }
      }
    });
  }
});

// Clear Cart

$(document).on("click", ".btn-clear_cart", function (e) {
  e.preventDefault();
  delete_cart_items();
});

function delete_cart_items() {
  localStorage.removeItem("cart");
  display_cart();
}

function show_message(prefix = "Great!", message, type = "success") {
  Swal.fire(prefix, message, type);
}

function paginate(total, current_page, limit) {
  var number_of_pages = total / limit;
  var i = 0;
  var pagination = `<div class="row p-2">
    <div class="col-12">
        <div class="d-flex justify-content-center">
            <ul class="pagination mb-0">`;
  pagination += `<li class="page-item"><a class="page-link" href="javascript:prev_page()" >Previous</a></li>`;
  var active = "";
  while (i < number_of_pages) {
    active = current_page == i ? "active" : "";
    pagination += `<li class="page-item ${active}"><a class="page-link" href="javascript:go_to_page(${limit},${i})" >${++i}</a></li>`;
  }
  pagination += `<li class="page-item"><a class="page-link" href="javascript:next_page()">Next</a></li>
                </ul>
            </div>
        </div>
    </div>`;
  $(".pagination-container").html(pagination);
}

function go_to_page(limit, page_number) {
  var total = $("#total_products").val();
  var category_id = $("#product_categories").val();
  var offset = page_number * limit;

  get_products(category_id, limit, offset);
  paginate(total, page_number, limit);

  $("#limit").val(limit);
  $("#offset").val(offset);
  $("#current_page").val(page_number);
}

function prev_page() {
  var current_page = $("#current_page").val();
  var total = $("#total_products").val();
  var limit = $("#limit").val();
  var prev_page = parseFloat(current_page) - 1;

  if (prev_page >= 0) {
    go_to_page(limit, prev_page);
  }
}

function next_page() {
  var current_page = $("#current_page").val();
  var total = $("#total_products").val();
  var limit = $("#limit").val();

  var number_of_pages = total / limit;
  var next_page = parseFloat(current_page) + 1;

  if (next_page < number_of_pages) {
    go_to_page(limit, next_page);
  }
}

// search products
$("#search_products").on("keyup", function (e) {
  e.preventDefault();
  var search = $(this).val();
  get_products("", 25, 0, search);
});

/* POS - Point of Sale system ends */
