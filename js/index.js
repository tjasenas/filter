const btnOpenModal = document.querySelector(".show-modal");

if (btnOpenModal) {
  const postsWrappers = document.querySelector(".ctx-posts");

  /* Popup window */

  const modal = document.querySelector(".modal");
  const overlay = document.querySelector(".overlay");
  const btnCloseModal = document.querySelector(".close-modal");
  let filterInt = false;

  const openModal = function () {
    modal.classList.remove("hidden");
    overlay.classList.remove("hidden");
    if (filterInt) return;

    fetchData();
    filterInt = true;
  };

  const closeModal = function () {
    modal.classList.add("hidden");
    overlay.classList.add("hidden");
  };

  btnOpenModal.addEventListener("click", openModal);
  btnCloseModal.addEventListener("click", closeModal);
  overlay.addEventListener("click", closeModal);

  document.addEventListener("keydown", function (event) {
    console.log(event.key);

    if (event.key === "Escape" && !modal.classList.contains("hidden")) {
      closeModal();
    }
  });

  /* Filtering  */

  const filterWrapper = document.querySelector(".posts-filtering");
  const activeTags = document.querySelector(".active-tags");
  const postsQty = document.querySelector(".posts-qty");
  const filterBtn = document.querySelector(".filter-btn");
  const clearFilters = document.querySelector(".clear-filters");
  const loadMore = document.querySelector(".load-more");

  const spinnerWrapper = document.querySelector(".spinner-wrapper");
  const buttonsWrapper = document.querySelector(".btn-wrapper");
  buttonsWrapper.classList.add("hidden");

  let tags = [
    { slug: activeTags.dataset.page, name: activeTags.dataset.title },
  ];

  let postType = activeTags.dataset.postType;
  let posts = [];
  let totalPosts = 0;
  const perPage = loadMore ? loadMore.dataset.perPage : 0;
  let page = 1;
  const textLong = 270;

  const displayedPosts = postsWrappers.children.length;
  let startFrom = displayedPosts;

  function truncate(string, limit) {
    if (string.length <= limit) {
      return string;
    }
    return string.slice(0, limit) + "...";
  }

  filterWrapper.querySelectorAll(".tag").forEach((e) => {
    const postsQty = +e.parentNode.querySelector(".has-posts").textContent;
    if (postsQty === 0) e.disabled = true;
  });

  const updateTags = () => {
    const getAllTags = activeTags.querySelectorAll(".remove-tag");
    if (!getAllTags) return;

    getAllTags.forEach((e) => {
      if (activeTags.dataset.page !== e.dataset.slug) {
        tags.push({
          slug: e.dataset.slug,
          name: e.dataset.name,
          parent: e.dataset.parent,
        });
      }
    });
  };

  updateTags();

  function listElement(e, isChecked) {
    return `
    <li>
      <input 
        class='tag' 
        id='${e.slug}' 
        data-parent="${e.parent}" 
        data-slug='${e.slug}' 
        data-name='${e.name}' 
        type='checkbox' 
        ${isChecked && "checked"}
      />
      <label for='${e.slug}'>
        ${e.name}<span class="has-posts">${e.has_posts}</span>
      </label>
    </li>`;
  }

  const isChecked = (slug) => {
    return tags.find((e) => e.slug === slug) ? 1 : 0;
  };

  const displayFilter = (data) => {
    let filters = btnOpenModal.dataset.filters;
    filters = filters.replace(/\s/g, "").split(",");

    filterWrapper.innerHTML = "";

    filters.forEach((filterSlug) => {
      let html = "";
      let isOpen = false;
      let postsQty = 0;
      let childPostsQty = 0;

      console.log(filterSlug);

      const getFilter = data.find(
        (filter) => filter["parent-slug"] === filterSlug
      );

      getFilter.childrens.forEach((child) => {
        if (child.childrens.length > 0) {
          let subList = "";
          let childHtml = "";

          child.childrens.forEach((childElement) => {
            childPostsQty += childElement.has_posts;

            if (isChecked(childElement.slug)) isOpen = true;

            if (childElement.has_posts !== 0) {
              childHtml += listElement(
                childElement,
                isChecked(childElement.slug)
              );
            }
          });

          if (childPostsQty !== 0) {
            const addStyle = isOpen && "display: block";
            subList = `<ul class='sub-menu' style='${addStyle}'>${childHtml}</ul>`;
            html += `<li class='has_childrens'><div class='box-heading'>${child.name} (${childPostsQty})</div> ${subList}</li>`;
          }
        } else {
          postsQty += child.has_posts;
          if (isChecked(child.slug)) isOpen = true;
          if (child.has_posts !== 0)
            html += listElement(child, isChecked(child.slug));
        }
      });

      if (postsQty + childPostsQty !== 0) {
        const addStyle = isOpen && "display: block";
        const filterHtml = `<div class="drop-down">
        <div class="drop-down__heading">
        <h4>${getFilter["parent-name"]} (${postsQty + childPostsQty})</h4>
        </div>
        <ul class="filters-list" style="${addStyle}"  id="">${html}</ul>
        </div>`;

        filterWrapper.insertAdjacentHTML("beforeend", filterHtml);
      }
    });

    jQuery(function ($) {
      $(".box-heading").on("click", function () {
        $(this).next().slideToggle();
      });

      $(".drop-down__heading").on("click", function () {
        $(this).next().slideToggle();
      });
    });
  };

  function displayPosts(posts, loadMore = false) {
    if (!loadMore) {
      postsWrappers.innerHTML = "";
      postsQty.textContent = totalPosts;
    }

    let html = "";
    posts.forEach((e) => {
      let meta = "";

      if (postType !== "ekspertai") {
        meta = ` <div class="post_meta">
        <span class="post_meta_item post_meta_likes trx_addons_icon-heart-empty"><span class="post_meta_number">${e.likes}</span></span>
        <a href="${e.url}"#comments" class="post_meta_item post_meta_comments icon-comment-light inited">
          <span class="post_meta_number">${e.comments}</span>
          <span class="post_meta_label">Comments</span>
        </a>
        </div>`;
      }

      let url = e.url;
      let wSearch = window.location.search;

      if (postType === "patarimai") {
        url = wSearch.length > 0 ? e.url + wSearch : e.url;
      }

      html += `
            <article class="post_layout_band post_with_users_like">
  
            <div class="post_featured with_thumb hover_none">
  
            <a href="${url}" rel="bookmark">
              ${e.img}
            </a>
  
            </div>
                <div class="post_content_wrap">
                    <div class="post_header entry-header">
                        <div class="post_meta">
                            <span class="post_meta_item post_categories cat_sep">
                                ${activeTags.dataset.title}
                            </span>
                        </div>
  
                        <h3 class="post_title entry-title">
                            <a href="${url}" rel="bookmark">
                              ${e.title}
                            </a>
                        </h3>
                    </div>
  
                    <div class="post_content entry-content">
                        <div class="post_content_inner">
                           ${truncate(e.shortContent, textLong)}
                        </div> 
                    </div>

                </div>
        </article>`;
    });
    postsWrappers.insertAdjacentHTML("beforeend", html);
  }

  async function fetchData() {
    try {
      spinnerWrapper.classList.remove("hidden");

      let updatedTags = [];

      if (tags[0].slug === "" && tags.length === 1) {
        updatedTags = [];
      } else {
        updatedTags = tags[0].slug === "" ? tags.slice(1) : tags;
      }

      const ar = {
        postType,
        tags: updatedTags,
        perPage,
        startFrom: 0,
      };

      const data = await fetch(
        `https://vivaldi.lt/wp-json/posts/v1/sortBy/filter`,
        {
          method: "POST",
          body: JSON.stringify(ar),
        }
      );

      if (!data.ok) {
        throw new Error("Serverio klaida. Prašome pamėginti veliau.");
      }

      const response = await data.json();

      buttonsWrapper.classList.remove("hidden");
      spinnerWrapper.classList.add("hidden");

      displayFilter(response.filters);

      totalPosts = response.allPosts;
      filterBtn.querySelector(".btn-qty").textContent = `(${totalPosts})`;

      posts = response.posts;
    } catch (err) {
      console.log(err);
    }
  }

  filterWrapper.addEventListener("click", function (e) {
    const target = e.target;
    if (!target.classList.contains("tag")) return; // Return if not tag element is clicked

    const tagQty = +target.parentNode.querySelector(".has-posts").textContent;
    if (tagQty === 0) return;

    if (!target.checked) {
      const slug = target.dataset.slug;
      tags = tags.filter((e) => e.slug !== slug);

      const searchParams = new URLSearchParams(window.location.search);
      searchParams.delete(target.dataset.parent);
      let par = "";

      if (Object.keys(searchParams).length !== 0) {
        par = window.location.pathname + "?" + searchParams.toString();
      } else {
        par = window.location.pathname;
      }

      history.pushState(null, "", par);

      fetchData();
      return;
    }

    const slug = target.dataset.slug;
    const tagName = target.dataset.name;
    const parent = target.dataset.parent;

    const hasFilter = tags.filter((e) => e === slug);
    if (hasFilter.length) return; // If we has same filter in array return

    // Add tag over Posts
    tags.push({
      slug: slug,
      name: tagName,
      parent,
    });

    const searchParams = new URLSearchParams("");
    tags.forEach((e, i) => (i !== 0 ? searchParams.set(e.parent, e.slug) : ""));
    const par = window.location.pathname + "?" + searchParams.toString();
    history.pushState(null, "", par);

    fetchData();
  });

  activeTags.addEventListener("click", async function (e) {
    const target = e.target;

    if (target.classList.contains("remove-all")) {
      tags = [
        { slug: activeTags.dataset.page, name: activeTags.dataset.title },
      ];
      activeTags.classList.add("hidden");
      activeTags.innerHTML = `<li class="remove-all">Isvalyti filtrus</li>`;

      await fetchData();
      filterBtn.querySelector(".btn-qty").textContent = `(${totalPosts})`;

      startFrom = document.querySelector(".ctx-posts").children.length;

      if (startFrom === +document.querySelector(".posts-qty").textContent) {
        loadMore.parentNode.classList.add("hidden");
      } else {
        loadMore.parentNode.classList.remove("hidden");
      }

      history.pushState(null, "", window.location.pathname);

      displayPosts(posts);
      return;
    }

    if (!target.classList.contains("remove-tag")) return;

    const slug = target.dataset.slug;
    tags = tags.filter((e) => e.slug !== slug);
    target.parentNode.remove();
    if (tags.length === 1) {
      activeTags.classList.add("hidden");
      activeTags.innerHTML = `<li class="remove-all">Isvalyti filtrus</li>`;
    }

    const searchParams = new URLSearchParams("");
    tags.forEach((e, i) => (i !== 0 ? searchParams.set(e.parent, e.slug) : ""));
    let par = window.location.pathname + "?" + searchParams.toString();

    if (tags.length === 1) {
      par = window.location.pathname;
    }

    history.pushState(null, "", par);

    filterBtn.querySelector(".btn-qty").textContent = `(${totalPosts})`;
    await fetchData();
    displayPosts(posts);

    startFrom = document.querySelector(".ctx-posts").children.length;

    if (startFrom === +document.querySelector(".posts-qty").textContent) {
      loadMore.parentNode.classList.add("hidden");
    } else {
      loadMore.parentNode.classList.remove("hidden");
    }
  });

  filterBtn.addEventListener("click", function () {
    activeTags.innerHTML = `<li class="remove-all">Isvalyti filtrus</li>`;

    tags.forEach((e, i) => {
      if (i === 0) return;
      const tag = `<li>${e.name} <span class="remove-tag" data-slug="${e.slug}">X</span></li>`;
      activeTags.insertAdjacentHTML("afterbegin", tag);
      activeTags.classList.remove("hidden");
    });

    console.log(tags);

    displayPosts(posts);

    startFrom = document.querySelector(".ctx-posts").children.length;

    if (startFrom === +document.querySelector(".posts-qty").textContent) {
      loadMore.parentNode.classList.add("hidden");
    } else {
      loadMore.parentNode.classList.remove("hidden");
    }

    console.log(tags);
    closeModal();
  });

  loadMore.addEventListener("click", async function () {
    let updatedTags = [];
    if (tags[0].slug === "" && tags.length === 1) {
      updatedTags = [];
    } else {
      updatedTags = tags[0].slug === "" ? tags.slice(1) : tags;
    }

    try {
      this.classList.add("loading");
      const ar = {
        postType,
        tags: updatedTags,
        perPage,
        startFrom,
      };

      const data = await fetch(
        `https://vivaldi.lt/wp-json/posts/v1/getMore/posts`,
        {
          method: "POST",
          body: JSON.stringify(ar),
        }
      );

      if (!data.ok) {
        throw new Error("Serverio klaida. Prašome pamėginti veliau.");
      }
      const response = await data.json();
      this.classList.remove("loading");

      displayPosts(response.posts, true);
      startFrom = document.querySelector(".ctx-posts").children.length;

      if (startFrom === +postsQty.textContent) {
        this.parentNode.classList.add("hidden");
      }

      const searchParams = new URLSearchParams(window.location.search);

      if (searchParams.get("pg")) {
        page = +searchParams.get("pg");
        searchParams.set("pg", ++page);
        const par = window.location.pathname + "?" + searchParams.toString();
        history.pushState(null, "", par);
      } else {
        searchParams.set("pg", ++page);
        const par = window.location.pathname + "?" + searchParams.toString();
        history.pushState(null, "", par);
      }
    } catch (err) {
      console.log(err);
    }
  });

  clearFilters.addEventListener("click", async function () {
    if (tags.length === 1) return;

    tags = [{ slug: activeTags.dataset.page, name: activeTags.dataset.title }];
    activeTags.classList.add("hidden");
    activeTags.innerHTML = `<li class="remove-all">Isvalyti filtrus</li>`;

    history.pushState(null, "", window.location.pathname);

    await fetchData();
    displayPosts(posts);
    postsQty.innerHTML = totalPosts;
    filterBtn.querySelector(".btn-qty").textContent = `(${totalPosts})`;
  });
}

const postsWrappers = document.querySelector(".ctx-posts");

let highPrice = 0;
let lowestPrice = 0;

function displayUserPosts(posts) {
  let html = "";
  posts.forEach((e) => {
    html += `
          <article class="post_layout_band post_with_users_like">

          <div class="post_featured with_thumb hover_none">

          <a href="${e.url}" rel="bookmark">
            ${e.img}
          </a>

          </div>
              <div class="post_content_wrap">
                  <div class="post_header entry-header">
                      <h3 class="post_title entry-title">
                          <a href="${e.url}" rel="bookmark">
                            ${e.title}
                          </a>
                      </h3>
                  </div>

                  <div class="post_content entry-content">
                      <div class="post_content_inner">
                         ${truncate(e.shortContent, textLong)}
                      </div> 
                  </div>
                  <div class="post_meta">
                  <span class="post_meta_item post_meta_likes trx_addons_icon-heart-empty"><span class="post_meta_number">${
                    e.likes
                  }</span></span>
                  <a href="${
                    e.url
                  }"#comments" class="post_meta_item post_meta_comments icon-comment-light inited">
                    <span class="post_meta_number">${e.comments}</span>
                    <span class="post_meta_label">Comments</span>
                  </a>
                  </div>
              </div>
      </article>`;
  });
  postsWrappers.insertAdjacentHTML("beforeend", html);
}

const getMore = document.querySelector(".get-more");

if (getMore) {
  const allUserPost = document.querySelector(".ctx-posts");
  const perPage = 3;
  let page = 1;

  const displayedPosts = allUserPost.children.length;
  let startFrom = displayedPosts;

  getMore.addEventListener("click", async function () {
    try {
      this.classList.add("loading");
      const ar = {
        postType: this.dataset.postType,
        postId: +this.dataset.id,
        perPage,
        startFrom,
      };

      console.log(ar);

      const data = await fetch(
        `https://vivaldi.lt/wp-json/posts/v1/getMoreUserPosts/posts`,
        {
          method: "POST",
          body: JSON.stringify(ar),
        }
      );

      if (!data.ok) {
        throw new Error("Serverio klaida. Prašome pamėginti veliau.");
      }

      const response = await data.json();
      this.classList.remove("loading");

      console.log(response);

      displayUserPosts(response.posts);
      startFrom = document.querySelector(".ctx-posts").children.length;

      console.log(startFrom, response.allPosts);
      if (startFrom === response.allPosts) {
        this.parentNode.classList.add("hidden");
      }
    } catch (err) {
      console.log(err);
    }
  });
}

function price(priceObj) {
  console.log(priceObj);
  return `
 <div class="drop-down">
     <div class="drop-down__heading">
     <h4>${priceObj.name}</h4>
     </div>
     <div class="filters-list" >
       <div class="price-input">
         <div class="field">
           <span>Min</span>
           <input type="number" class="input-min" value="${priceObj.lowest_price}">
         </div>
         <div class="separator">-</div>
         <div class="field">
           <span>Maks</span>
           <input type="number" class="input-max" value="${priceObj.hight_price}">
         </div>
       </div>
       <div class="slider">
         <div class="progress"></div>
       </div>
       <div class="range-input">
         <input type="range" class="range-min" min="${priceObj.startLowest}" max="${priceObj.startHights}" value="${priceObj.lowest_price}" step="1">
         <input type="range" class="range-max" min="${priceObj.startLowest}" max="${priceObj.startHights}" value="${priceObj.hight_price}" step="1">
       </div>
     </div>
 </div>`;
}

// const openFilterModal = document.querySelector(".open-products-filter");
const openFilterBtn = document.querySelector(".open-products-filter");

const modal = document.querySelector(".modal");
const overlay = document.querySelector(".overlay");
const btnCloseModal = document.querySelector(".close-modal");
const filterBtn = document.querySelector(".filter-btn");
const filterBlock = document.querySelector(".posts_sorting");
const tagsWrapper = document.querySelector(".active-tags");

let init = false;
let tags = [];

const queryString = window.location.search;
if (queryString) {
  const urlParams = new URLSearchParams(queryString);
  urlParams.forEach((value, key) => {
    tags.push({ id: Date.now(), tag: value, parent: key });
  });
  init = true;
}
console.log(tags);

const openModal = function () {
  modal.classList.remove("hidden");
  overlay.classList.remove("hidden");
};

const closeModal = function () {
  modal.classList.add("hidden");
  overlay.classList.add("hidden");
};

openFilterBtn.addEventListener("click", function () {
  init ? getFilteredProducts() : fetchFilter();
  console.log(init);

  openModal();
});
filterBtn.addEventListener("click", function () {
  closeModal();
});
btnCloseModal.addEventListener("click", closeModal);
overlay.addEventListener("click", closeModal);

document.addEventListener("keydown", function (event) {
  console.log(event.key);

  if (event.key === "Escape" && !modal.classList.contains("hidden")) {
    closeModal();
  }
});

function fixText(text) {
  if (text.includes("pa_")) {
    const str = text.slice(3).split("-").join(" ");
    console.log(str);
    return str[0].toUpperCase() + str.slice(1);
  }
  return text[0].toUpperCase() + text.slice(1);
}

const pageTitle = document.querySelector(".page-title");
const productsWrapper = document.querySelector(".products");

const filtersWrapper = document.querySelector(".posts-filtering");
const clearFilters = document.querySelector(".clear-filters");
const spinnerWrapper = document.querySelector(".spinner-wrapper");
const loadMoreProductsBtn = document.querySelector(".get-more-products");

btnCloseModal.addEventListener("click", closeModal);

function filters(data, startLow, startHigh) {
  let html = "";

  data.forEach((e) => {
    let listElement = "";
    let isOpen = false;
    let i = 0;

    if (e.name === "Kaina") {
      // console.log({ ...e, startLowest: lowestPrice, startHights: highPrice });
      html = price({ ...e, startLowest: startLow, startHights: startHigh });
      return;
    }

    e.attr.forEach((item) => {
      hasActiveTag = tags.find((e) => e.tag === item.title);
      if (hasActiveTag) isOpen = true;

      listElement += `<li>
        <input class="tag" data-filter="${e.name}" id="tag-${
        item.title
      }" data-tax="${item.title}"  type="checkbox" ${
        hasActiveTag ? "checked" : ""
      }>
        <label for="tag-${item.title}">
          ${item.title}<span class="has-posts">${item.qty}</span>
        </label>
      </li>`;
    });

    html += `
    <div class="drop-down">
        <div class="drop-down__heading">
        <h4>${fixText(e.name)}</h4>
        </div>
        <ul class="filters-list" style="${isOpen ? "display:block" : ""}" id="">
          ${listElement}
        </ul>
    </div>`;
  });

  filtersWrapper.innerHTML = html;

  jQuery(function ($) {
    $(".box-heading").on("click", function () {
      $(this).next().slideToggle();
    });

    $(".drop-down__heading").on("click", function () {
      $(this).next().slideToggle();
    });
  });

  const rangeInput = document.querySelectorAll(".range-input input"),
    priceInput = document.querySelectorAll(".price-input input"),
    range = document.querySelector(".slider .progress");
  let priceGap = 0;

  priceInput.forEach((input) => {
    let myTimeout = "";
    input.addEventListener("input", (e) => {
      let minPrice = parseInt(priceInput[0].value),
        maxPrice = parseInt(priceInput[1].value);

      if (maxPrice - minPrice >= priceGap && maxPrice <= rangeInput[1].max) {
        if (e.target.className === "input-min") {
          rangeInput[0].value = minPrice;
          // range.style.left = (minPrice / rangeInput[0].max) * 100 + "%";
        } else {
          rangeInput[1].value = maxPrice;
          // range.style.right = 100 - (maxPrice / rangeInput[1].max) * 100 + "%";
        }
      }

      if (minPrice > maxPrice) return;
      if (minPrice < 0 || isNaN(minPrice)) return;

      const tagIndex = tags.findIndex((e) => e.parent === "kaina");
      if (tagIndex !== -1) {
        tags[tagIndex].tag = minPrice + "-" + maxPrice;
      } else {
        tags.push({
          id: Date.now(),
          tag: minPrice + "-" + maxPrice,
          parent: "kaina",
        });
      }

      const searchParams = new URLSearchParams("");
      tags.forEach((e) => searchParams.append(e.parent, e.tag));
      const par = window.location.pathname + "?" + searchParams.toString();
      history.pushState(null, "", par);

      clearTimeout(myTimeout);
      myTimeout = setTimeout(() => getFilteredProducts(), 1000);
    });
  });

  rangeInput.forEach((input) => {
    let myTimeout = "";
    input.addEventListener("input", (e) => {
      let minVal = parseInt(rangeInput[0].value),
        maxVal = parseInt(rangeInput[1].value);

      if (maxVal - minVal < priceGap) {
        if (e.target.className === "range-min") {
          rangeInput[0].value = maxVal - priceGap;
        } else {
          rangeInput[1].value = minVal + priceGap;
        }
      } else {
        priceInput[0].value = minVal;
        priceInput[1].value = maxVal;
        // range.style.left = (minVal / rangeInput[0].max) * 100 + "%";
        // range.style.right = 100 - (maxVal / rangeInput[1].max) * 100 + "%";
      }

      if (minVal > maxVal) return;
      if (minVal < 0 || isNaN(minVal)) return;

      const tagIndex = tags.findIndex((e) => e.parent === "kaina");
      if (tagIndex !== -1) {
        tags[tagIndex].tag = minVal + "-" + maxVal;
      } else {
        tags.push({
          id: Date.now(),
          tag: minVal + "-" + maxVal,
          parent: "kaina",
        });
      }

      const searchParams = new URLSearchParams("");
      tags.forEach((e) => searchParams.append(e.parent, e.tag));
      const par = window.location.pathname + "?" + searchParams.toString();
      history.pushState(null, "", par);

      clearTimeout(myTimeout);
      myTimeout = setTimeout(() => getFilteredProducts(), 1000);
    });
  });
}

async function fetchFilter() {
  try {
    spinnerWrapper.classList.remove("hidden");
    const response = await fetch(
      `https://localhost/shop/wp-json/category/products/v1/${openFilterBtn.dataset.category}`
    );

    if (!response.ok) {
      throw new Error("Serverio klaida. Prašome pamėginti veliau.");
    }

    const data = await response.json();

    lowestPrice = data.lowest_price;
    highPrice = data.high_price;

    filters(data.filters, data.lowest_price, data.high_price);
    filterBtn.querySelector(".btn-qty").textContent = data.qty;
    spinnerWrapper.classList.add("hidden");
    init = true;
  } catch (err) {
    console.log(err);
  }
}

clearFilters.addEventListener("click", async function () {
  try {
    if (!tags.length) return;
    tags = [];
    spinnerWrapper.classList.remove("hidden");
    history.pushState(null, "", window.location.pathname);
    const response = await fetch(
      `http://localhost/shop/wp-json/category/reset/v1/${openFilterBtn.dataset.category}`
    );

    if (!response.ok) {
      throw new Error("Serverio klaida. Prašome pamėginti veliau.");
    }

    const data = await response.json();
    filters(data.filters, data.qty);
    productsWrapper.innerHTML = data.products;
    filterBtn.querySelector(".btn-qty").textContent = data.qty;
    spinnerWrapper.classList.add("hidden");
    addFilters();
  } catch (err) {
    console.log(err);
  }

  // if (!tags.length) return;

  // tags = [];
  // //Get filtered products
  // getFilteredProducts();
  // history.pushState(null, "", window.location.pathname);
});

async function getFilteredProducts() {
  try {
    spinnerWrapper.classList.remove("hidden");
    productsWrapper.classList.add("loading");

    // const findObj = tags.find((e) => e.parent === "kaina");

    // const sendTags = findObj
    //   ? tags
    //   : [...tags, { tag: lowestPrice + "-" + highPrice, parent: "kaina" }];

    const response = await fetch(
      `http://localhost/shop/wp-json/product/filter/v1/${openFilterBtn.dataset.category}`,
      {
        method: "POST",
        body: JSON.stringify({ tags }),
      }
    );

    if (!response.ok) {
      throw new Error("Serverio klaida. Prašome pamėginti veliau.");
    }

    const data = await response.json();
    if (data.code === "empty_category") {
      filtersWrapper.innerHTML = `<p>${data.message}</p>`;
      filterBtn.querySelector(".btn-qty").textContent = 0;
      spinnerWrapper.classList.add("hidden");
      return;
    }

    filters(data.filters, data.lowest_price, data.high_price);
    productsWrapper.innerHTML = data.products;
    filterBtn.querySelector(".btn-qty").textContent = data.qty;
    spinnerWrapper.classList.add("hidden");
    productsWrapper.classList.remove("loading");
    init = true;
    addFilters();

    console.log(tags);
  } catch (err) {
    console.log(err);
    filtersWrapper.innerHTML = `<p>${err.message}</p>`;
    spinnerWrapper.classList.add("hidden");
    filterBtn.querySelector(".btn-qty").textContent = 0;
    productsWrapper.innerHTML = `<p>${err.message}</p>`;
    productsWrapper.classList.remove("loading");
  }
}

filtersWrapper.addEventListener("click", function (e) {
  const target = e.target;
  const targetIsTag = e.target.classList.contains("tag");

  if (targetIsTag) {
    //Add tags

    const hasTag = tags.find((e) => e.tag === target.dataset.tax);
    if (hasTag) {
      tags = tags.filter((tag) => tag.tag !== target.dataset.tax);
    } else {
      tags.push({
        id: Date.now(),
        tag: target.dataset.tax,
        parent: target.dataset.filter,
      });
    }

    //Get filtered products
    getFilteredProducts();

    // Add query to url
    const searchParams = new URLSearchParams("");
    tags.forEach((e) => searchParams.append(e.parent, e.tag));
    const par = window.location.pathname + "?" + searchParams.toString();
    history.pushState(null, "", par);
  }
});

tagsWrapper.addEventListener("click", function (e) {
  const target = e.target;

  const isRemoveAll = target.classList.contains("remove-all");
  if (isRemoveAll) {
    tags = [];
    history.pushState(null, "", window.location.pathname);
    getFilteredProducts();
  }

  const isRemoveTag = target.classList.contains("remove-tag");
  if (isRemoveTag) {
    tags = tags.filter((tag) => tag.id !== +target.dataset.id);
    history.pushState(null, "", window.location.pathname);
    getFilteredProducts();
  }
});

function addFilters() {
  const newTags = tags.map((e) => {
    return `<li>${fixText(e.parent)} - ${
      e.tag
    }<span class="remove-tag" data-id="${e.id}">X</span></li>`;
  });
  newTags.push('<li class="remove-all">Išvalyti filtrus</li>');
  tagsWrapper.innerHTML = newTags.join("");

  if (tags.length === 0) {
    tagsWrapper.classList.add("hidden");
  } else {
    tagsWrapper.classList.remove("hidden");
  }
}

async function loadMoreProducts() {
  try {
    loadMoreProductsBtn.classList.add("loading");
    const response = await fetch(
      `https://localhost/shop/wp-json/get-more/products/v1/${loadMoreProductsBtn.dataset.category}`
    );

    if (!response.ok) {
      throw new Error("Serverio klaida. Prašome pamėginti veliau.");
    }

    loadMoreProductsBtn.classList.remove("loading");
  } catch (err) {
    console.log(err);
  }
}
