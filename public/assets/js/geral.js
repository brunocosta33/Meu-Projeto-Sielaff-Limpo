// == Functions
// scroll position
$.fn.isInViewport = function (direction) {
  const elementTop = $(this).offset().top;
  const elementBottom = elementTop + $(this).outerHeight();
  const viewportTop = $(window).scrollTop();
  let viewportBottom = viewportTop + $(window).height();

  if (direction === "down") {
    viewportBottom -= $(window).height() * 0.1;
  }

  return elementBottom > viewportTop && elementTop < viewportBottom;
};

let direction = "down";
let scrollTop = $(window).scrollTop();

function scrollDetect() {
  const currentScrollTop = $(this).scrollTop();
  direction = currentScrollTop > scrollTop ? "down" : "up";
  scrollTop = currentScrollTop;

  $(".scroll-detect").each(function () {
    const $this = $(this),
      thisClass = $this.attr("class");

    if ($this.isInViewport(direction)) {
      $this.addClass("in-view");
    } 
  });
}

$(function () {
  // curent year footer
  var year = new Date().getFullYear();
  if ($(".current-year").length) $(".current-year").text(year);

  // == custom selects ==
  $("select.custom-select").each(function () {
    var $this = $(this),
      numberOfOptions = $this.children("option").length,
      dropdownClass = $this.data("dropdown-class") || "",
      btnClass = $this.data("btn-class") || "btn";

    $this.wrap('<div class="dropdown custom-select-wrapper ' + dropdownClass + '"></div>');
    $this.after('<button class="' + btnClass + ' dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><span class="text text-truncate"></span></button>');

    var $styledSelect = $this.next(".dropdown-toggle");

    $styledSelect.children(".text").text($this.children("option").eq(0).text());

    var $list = $("<ul />", {
      class: "dropdown-menu options",
    }).insertAfter($styledSelect);

    for (var i = 0; i < numberOfOptions; i++) {
      $("<li />", {
        text: $this.children("option").eq(i).text(),
        rel: $this.children("option").eq(i).val(),
      }).appendTo($list);
    }

    var $listItems = $list.children("li");

    $listItems.addClass("cursor-pointer dropdown-item");

    var selectedIndex = $this.prop("selectedIndex");
    $listItems.removeClass("active");
    $listItems.eq(selectedIndex).addClass("active");

    $listItems.click(function (e) {
      e.stopPropagation();
      $listItems.removeClass("active");
      $(this).addClass("active");
      $styledSelect.children(".text").text($(this).text());
      $this.val($(this).attr("rel"));
      $this.trigger("change");
      $styledSelect.dropdown("hide");
    });

    $(document).click(function () {
      $styledSelect.removeClass("active");
    });
  });

  // == custom QTD Inputs ==
  function quantityButtons(el, amount) {
    const input = el.closest(".custom-qtd-input").find("input");
    var value = input.val();
    if (!isNaN(value)) {
      value = parseInt(value) + amount;
      if (value > 0) {
        input.val(value);
      }
    }
  }

  $(".custom-qtd-input .qtd-more").on("click", function (event) {
    quantityButtons($(this), 1);
  });

  $(".custom-qtd-input .qtd-less").on("click", function (event) {
    quantityButtons($(this), -1);
  });

  // form validations
  // Fetch all the forms we want to apply custom Bootstrap validation styles to
  const forms = document.querySelectorAll(".needs-validation");

  // Loop over them and prevent submission
  Array.from(forms).forEach((form) => {
    form.addEventListener(
      "submit",
      (event) => {
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
        }

        form.classList.add("was-validated");
      },
      false
    );
  });

  // == sliders ==
  $.getScript("https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js", function () {
    $("head").find('[rel="stylesheet"]').first().before('<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">');

    const swiperIntro = new Swiper(".swiper-homepage-intro, .swiper-folheto-intro", {
      loop: true,
      autoplay: true,
      speed: 1000,
      navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
      },
    });

    $(".swiper").addClass("visible");
  });

  // anchor menus homepage href
  $(".main-menu-wrapper .anchor-link").each(function () {
    var hrefValue = $(this).attr("href"),
      targetID = hrefValue.replace("/", "", 1);
    if ($(targetID).length) {
      $(this).attr("href", targetID);
    }
  });
  // menu click hide navbar on mobile
  $("body").on("click", ".main-menu-offcanvas.show a", function () {
    $(".main-menu-offcanvas").offcanvas("hide");
  });

  // Prod name on modal
  $("button[data-bs-target='#modalProdInfo']").on("click", function () {
    var prodName = $(this).find(".prod-list-name").text();
    $("#inputProdName").val(prodName);
  });
  $("#modalProdInfo").on("hidden.bs.modal", function() {
    $("#inputProdName").val("");
    console.log("hidden")
  });

  setTimeout(scrollDetect, 100);
});

$(window).on("resize scroll", scrollDetect);