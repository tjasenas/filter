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

  let tags = [{ slug: activeTags.dataset.page, name: activeTags.dataset.title }];

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

      const getFilter = data.find((filter) => filter["parent-slug"] === filterSlug);

      getFilter.childrens.forEach((child) => {
        if (child.childrens.length > 0) {
          let subList = "";
          let childHtml = "";

          child.childrens.forEach((childElement) => {
            childPostsQty += childElement.has_posts;

            if (isChecked(childElement.slug)) isOpen = true;

            if (childElement.has_posts !== 0) {
              childHtml += listElement(childElement, isChecked(childElement.slug));
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
          if (child.has_posts !== 0) html += listElement(child, isChecked(child.slug));
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

      const data = await fetch(`https://vivaldi.lt/wp-json/posts/v1/sortBy/filter`, {
        method: "POST",
        body: JSON.stringify(ar),
      });

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
      tags = [{ slug: activeTags.dataset.page, name: activeTags.dataset.title }];
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

      const data = await fetch(`https://vivaldi.lt/wp-json/posts/v1/getMore/posts`, {
        method: "POST",
        body: JSON.stringify(ar),
      });

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
                  <span class="post_meta_item post_meta_likes trx_addons_icon-heart-empty"><span class="post_meta_number">${e.likes}</span></span>
                  <a href="${e.url}"#comments" class="post_meta_item post_meta_comments icon-comment-light inited">
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

      const data = await fetch(`https://vivaldi.lt/wp-json/posts/v1/getMoreUserPosts/posts`, {
        method: "POST",
        body: JSON.stringify(ar),
      });

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

const openFilterModal = document.querySelector(".open-products-filter");

const modal = document.querySelector(".modal");
const overlay = document.querySelector(".overlay");
const btnCloseModal = document.querySelector(".close-modal");

const tags = [];

const openModal = function () {
  modal.classList.remove("hidden");
  overlay.classList.remove("hidden");
};

const closeModal = function () {
  modal.classList.add("hidden");
  overlay.classList.add("hidden");
};

openFilterModal.addEventListener("click", openModal);
btnCloseModal.addEventListener("click", closeModal);
overlay.addEventListener("click", closeModal);

document.addEventListener("keydown", function (event) {
  console.log(event.key);

  if (event.key === "Escape" && !modal.classList.contains("hidden")) {
    closeModal();
  }
});

const pageTitle = document.querySelector(".page-title");
const productsWrapper = document.querySelector(".products");
const filterBtn = document.querySelector(".open-products-filter");
const filtersWrapper = document.querySelector(".posts-filtering");

function filters(data) {
  let html = "";

  data.forEach((e) => {
    let listElement = "";

    e.attr.forEach((item) => {
      listElement += `<li>
        <input class="tag" data-filter="${e.name}" id="tag-${item.title}" data-tax="${item.title}"  type="checkbox" >
        <label for="tag-${item.title}">
          ${item.title}<span class="has-posts">${item.qty}</span>
        </label>
      </li>`;
    });

    html += `
    <div class="drop-down">
        <div class="drop-down__heading">
        <h4>${e.name} (1)</h4>
        </div>
        <ul class="filters-list" id="">
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
}

filterBtn.addEventListener("click", async function () {
  try {
    const response = await fetch(`https://localhost/shop/wp-json/category/products/v1/accessories`);

    if (!response.ok) {
      throw new Error("Serverio klaida. Prašome pamėginti veliau.");
    }

    const data = await response.json();

    console.log(data);

    filters(data.filters);

    productsWrapper.innerHTML = data.products;
  } catch (err) {
    console.log(err);
  }
});

async function getFilteredProducts() {
  try {
    const response = await fetch(`https://localhost/shop/wp-json/product/filter/v1/filter`, {
      method: "POST",
      body: JSON.stringify({ tags }),
    });

    if (!response.ok) {
      throw new Error("Serverio klaida. Prašome pamėginti veliau.");
    }

    const data = await response.json();

    filters(data.filters);
    return;
    productsWrapper.innerHTML = data.products;
  } catch (err) {
    console.log(err);
  }
}

filtersWrapper.addEventListener("click", function (e) {
  const target = e.target;
  const targetIsTag = e.target.classList.contains("tag");

  if (targetIsTag) {
    //Add tags
    tags.push({ tag: target.dataset.tax, parent: target.dataset.filter });

    //Get filtered products
    getFilteredProducts();

    // Add query to url
    const searchParams = new URLSearchParams("");
    tags.forEach((e) => searchParams.append(e.parent, e.tag));
    const par = window.location.pathname + "?" + searchParams.toString();
    history.pushState(null, "", par);
  }
});
