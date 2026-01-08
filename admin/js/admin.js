(function ($, window, document) {
  "use strict";

  // Ensure the DOM is fully loaded before initializing
  document.addEventListener("DOMContentLoaded", function () {
    // Initialize the AIPV_ADMIN class instance and call the initialization method
    const admin = new AIPV_ADMIN();
    admin.initialize();
  });
})(jQuery, window, document);

// Main AIPV_ADMIN class
class AIPV_ADMIN {
  constructor() {
    // Set AJAX URL and nonce from localized script (ensures secure requests)
    this._ajaxURL = aipv_obj.ajax_url;
    this._nonce = aipv_obj.aipv_nonce;

    // Global variables
    this.aipv = "";
    this.sidebar = "";
    this.postView = "";
    this.generateView = "";
    this.settingsView = "";
    this.searchInput = "";
    this.postWrapper = "";
    this.postWrapperLoadMore = "";
    this.renderedImages = "";
    this.renderedImagesWrapper = "";
    this.revertToOriginal = "";
  }

  initialize() {
    // Setup global variables
    this.setupGlobalVariables();

    // Run the initialization function
    this.initEventHandlers();
  }

  /***
   * GLOBAL FUNCTIONS
   ***/

  setupGlobalVariables() {
    // Setup global element variables
    this.aipv = document.getElementById("aipv-admin-view");
    this.sidebar = this.aipv.querySelector(".sidebar");

    // Setup posts view variables
    this.postView = this.aipv.querySelector(".template.template-posts");
    this.searchInput = this.aipv.querySelector(".search-bar .search-input");
    this.postWrapper = this.aipv.querySelector(".posts-section .posts-wrapper");
    this.postWrapperLoadMore = this.aipv.querySelector(".load-more");

    // Set up generate view variables
    this.generateView = this.aipv.querySelector(".template.template-generate");
    this.renderedImages = this.aipv.querySelector(".rendered-images");
    this.renderedImagesWrapper =
      this.renderedImages.querySelector(".images-wrapper");
    this.revertToOriginal = this.generateView.querySelector(
      ".revert-to-original",
    );

    // Set up settings view variables
    this.settingsView = this.aipv.querySelector(".template.template-settings");
  }

  initEventHandlers() {
    // Initialize events
    this.modeToggle();
    this.dropdownClickEvents();
    this.resetFilters();
    this.sidebarClickEvent();
    this.searchBarChangeEvent();
    this.keywordSearchChangeEvent();
    this.numberInputChangeEvent();
    this.resolutionSelectChangeEvent();
    this.renderButtonClickEvent();
    this.revertToOriginalClickEvent();
    this.goBackToPostsClickEvent();
    this.postCardClickEvent();
    this.historyLoadImagesClickEvent();
    this.historyPromptLoadMoreButtonCheck();
    this.checkQueryParams();
    this.dalleAPIKeyInputChangeEvent();
    this.dropdownItemClickEvent();
    this.signUpTextClickEvent();
    this.loadMoreClickEvent();
    this.dataRetentionToggleClickEvent();
  }

  checkQueryParams() {
    // Create URL object and check if 'tab' exists in the query params
    const url = new URL(window.location.href);
    const hasTab = url.searchParams.has("tab");

    // Determine mainUrl and currentTab based on the presence of 'tab' parameter
    const mainUrl = hasTab ? url.toString().split("&tab")[0] : url;
    const currentTab = hasTab
      ? url.searchParams.get("tab")
      : (
          this.sidebar.querySelector(".item.active") ||
          this.sidebar.querySelector(".item")
        ).dataset.tab;

    // Update active state for sidebar items
    this.sidebar
      .querySelectorAll(".item")
      .forEach((item) => item.classList.remove("active"));
    const currentSidebarItem = this.sidebar.querySelector(
      `.item[data-tab="${currentTab}"]`,
    );
    if (currentSidebarItem) {
      currentSidebarItem.classList.add("active");
    }

    // Update active state for templates
    this.aipv
      .querySelectorAll(".main-content .template")
      .forEach((item) => item.classList.remove("active"));
    const currentTemplate = this.aipv.querySelector(
      `.main-content .template[data-tab="${currentTab}"]`,
    );
    if (currentTemplate) {
      currentTemplate.classList.add("active");
    }

    // Scroll the window to the top of the page smoothly
    window.scrollTo({ top: 0, behavior: "smooth" });

    // Update the browser's history to reflect the current tab in the URL
    window.history.replaceState({}, "", `${mainUrl}&tab=${currentTab}`);
  }

  async genericFetchRequest(_$data, _method = "GET") {
    // Try/Catch
    try {
      // Setup options object
      let options = {
        method: _method,
      };

      // Set url
      let url = this._ajaxURL;

      // Set nonce to data
      _$data.append("aipv_nonce", this._nonce);

      // Set the body only for POST (or other methods that allow a body)
      if (_method === "POST") {
        options.body = _$data;
      }

      // For GET requests, append data as query parameters to the URL
      if (_method === "GET") {
        const queryParams = new URLSearchParams(_$data).toString();
        url += `?${queryParams}`;
      }

      // Send fetch request and wait for the response
      const _$response = await fetch(url, options);

      // Check if the response status is OK (200)
      if (_$response.ok) {
        const _$result = await _$response.json();
        return _$result;
      } else {
        // Handle non-OK response here
        console.error("Fetch failed with status:", _$response.status);
        return null;
      }
    } catch (error) {
      // Handle any errors that occur during the fetch request
      console.error(error);
      return null;
    }
  }

  /***
   * SIDEBAR FUNCTIONS
   ***/

  modeToggle() {
    // Add change event for mode toggling between light and dark
    this.aipv.querySelectorAll(".mode-toggle .mode").forEach((mode) => {
      mode.addEventListener("click", async () => {
        // Set modeString
        let modeString = mode.classList.contains("light") ? "light" : "dark";

        // Toggle active states between light/dark
        this.aipv.classList.toggle("light", modeString === "light");
        mode.classList.add("active");
        mode.nextElementSibling?.classList.remove("active");
        mode.previousElementSibling?.classList.remove("active");

        // Send AJAX request to update viewer mode option
        const _$data = new FormData();
        _$data.append("action", "aipv_update_viewer_mode");
        _$data.append("mode", modeString);
        await this.genericFetchRequest(_$data);
      });
    });
  }

  sidebarClickEvent() {
    // Set sidebar items
    const sidebarItems = this.sidebar.querySelectorAll(".item");

    // Set click events for each sidebar item
    sidebarItems.forEach((item) => {
      item.addEventListener("click", () => {
        // Reset
        this.renderedImages.classList.remove("loaded");
        this.renderedImagesWrapper.innerHTML = "";
        this.resetGenerateView();

        // Set active sidebar item
        const tab = item.dataset.tab;
        this.setActiveSidebarItem(item, tab);

        // Scroll to top
        window.scrollTo({ top: 0, behavior: "smooth" });

        // Update url
        const url = new URL(window.location.href);
        const mainUrl = url.toString().split("&tab")[0];
        window.history.replaceState({}, "", `${mainUrl}&tab=${tab}`);
      });
    });
  }

  setActiveSidebarItem(item, tab) {
    // Reset sidebar item active states
    this.sidebar
      .querySelectorAll(".item")
      .forEach((el) => el.classList.remove("active"));
    item.classList.add("active");

    // Reset templayte view active states
    document
      .querySelectorAll(".main-content .template")
      .forEach((el) => el.classList.remove("active"));
    document
      .querySelector(`.main-content .template[data-tab="${tab}"]`)
      .classList.add("active");
  }

  /***
   * POSTS FUNCTIONS
   ***/

  async filtering(_paged = 1) {
    // Set search value and create FormData object
    const search = this.searchInput.value;
    const _$data = new FormData();
    _$data.append("action", "aipv_get_posts");

    // Loop through active filters and set postType, order, and date if present
    this.aipv.querySelectorAll(".type-block.active").forEach((item) => {
      if (Boolean(item.dataset.type)) {
        _$data.append("post_type", item.dataset.type);
      }
      if (Boolean(item.dataset.alphabetical)) {
        _$data.append("alphabetical", item.dataset.alphabetical);
      }
      if (Boolean(item.dataset.date)) {
        _$data.append("date", item.dataset.date);
      }
    });

    // Set search and exclude if present
    if (search) {
      _$data.append("search", search);
    }

    // Add pagination parameter
    _$data.append("paged", _paged);

    // Run fetch request
    const _$fetchRequest = await this.genericFetchRequest(_$data);

    // Update post wrapper content based on excluded types
    this.postWrapper.innerHTML =
      _paged > 1
        ? this.postWrapper.innerHTML + _$fetchRequest.content
        : _$fetchRequest.content;

    // Toggle "load more" button visibility based on total posts
    this.postWrapperLoadMore.classList.toggle(
      "hidden",
      _$fetchRequest.total_posts <= 18 * _paged,
    );

    // Set current page attribute for the wrapper (track pagination)
    this.postWrapper.setAttribute("data-current-page", _paged);

    // Set post card click events and end loading animation
    this.postCardClickEvent();
    this.postWrapperLoadMore
      .querySelector(".load-more-text")
      .classList.remove("loading");
    this.postWrapperLoadMore
      .querySelector(".rc-loader")
      .classList.remove("loading");
  }

  resetFilters() {
    // Reset filter button click event
    this.postView
      .querySelector(".filter-reset")
      .addEventListener("click", (e) => {
        // Prevent default behavior
        e.preventDefault();

        // Clear search and dropdown filters
        this.searchInput.value = "";
        this.aipv
          .querySelectorAll(".type-block.active")
          .forEach((typeBlock) => typeBlock.classList.remove("active"));
        this.aipv
          .querySelector('.type-block[data-type="any"]')
          .classList.add("active");

        // Start filtering
        this.filtering();
      });
  }

  searchBarChangeEvent() {
    // Search bar change event
    this.searchInput.addEventListener("change", (e) => {
      // Prevent default behavior
      e.preventDefault();

      // Start filtering
      this.filtering();
    });
  }

  dropdownClickEvents() {
    // Set dropdowns
    const dropdowns = this.aipv.querySelectorAll(".dropdowns .dropdown .title");

    // Add click event to each dropdown
    dropdowns.forEach((title) => {
      title.addEventListener("click", () => {
        // Get dropdown variables
        const parentDropdown = title.closest(".dropdown");

        // Update active filter dropdown
        this.aipv
          .querySelectorAll(".dropdowns .dropdown")
          .forEach((dropdown) => {
            if (dropdown != parentDropdown) {
              dropdown.classList.remove("active");
            }
          });
        parentDropdown.classList.toggle("active");
      });
    });

    // Add a click event listener to the document
    document.addEventListener("click", (event) => {
      const dropdown = this.aipv.querySelector(".dropdowns .dropdown.active");

      // Check if the clicked element is not inside the dropdown
      if (dropdown && !dropdown.contains(event.target)) {
        // If clicked outside the dropdown, remove the 'active' class
        dropdown.classList.remove("active");
      }
    });
  }

  dropdownItemClickEvent() {
    // Select all elements with the class 'type-block' and add a click event listener to each
    this.aipv.querySelectorAll(".type-block").forEach((typeBlock) => {
      // Add click event listener to each 'type-block'
      typeBlock.addEventListener("click", (e) => {
        // Prevent the default behavior
        e.preventDefault();

        // Check if the clicked 'type-block' already has the 'active' class
        if (typeBlock.classList.contains("active")) {
          // If it is active, remove the 'active' class
          typeBlock.classList.remove("active");
        } else {
          // Otherwise, if the 'type-block' is inside an dropdown with the class 'sort'
          const dropdown = typeBlock.closest(".dropdown");

          if (dropdown && dropdown.classList.contains("sort")) {
            // Remove the 'active' class from any other 'type-block' inside the 'dropdown.sort'
            this.aipv
              .querySelectorAll(".dropdown.sort .type-block.active")
              .forEach((activeBlock) => {
                activeBlock.classList.remove("active");
              });
          } else {
            // If it's not inside 'dropdown.sort', remove the 'active' class from 'type-block' within the closest dropdown
            dropdown
              .querySelectorAll(".type-block.active")
              .forEach((activeBlock) => {
                activeBlock.classList.remove("active");
              });
          }

          // Add the 'active' class to the clicked 'type-block'
          typeBlock.classList.add("active");
        }

        // Check if no 'type-block' elements are active inside '.post-types'
        if (
          this.aipv.querySelectorAll(".post-types .type-block.active")
            .length === 0
        ) {
          // If none are active, activate the 'type-block' with data-type="any"
          this.aipv
            .querySelector('.post-types .type-block[data-type="any"]')
            .classList.add("active");
        }

        // Call the filtering function
        this.filtering();
      });
    });
  }

  postCardClickEvent() {
    // Loop through post card buttons and add click events
    this.aipv.querySelectorAll(".post-card").forEach((card) => {
      card.addEventListener("click", async () => {
        // Set element variables
        const featuredImg = this.generateView.querySelector(".featured-img");
        const currentPostTitle = this.generateView.querySelector(
          ".current-post-title",
        );

        // Clear featured image and post title
        featuredImg.innerHTML = "";
        featuredImg.style.backgroundImage = "";
        currentPostTitle.innerHTML = "";

        // Get post ID and title
        if (!card) return;
        const postId = card.dataset.post;
        const postTitleElement = card.querySelector(".card-title .text");
        const postTitle = postTitleElement ? postTitleElement.innerHTML : "";

        // Update sidebar and template classes
        this.sidebar.querySelectorAll(".item").forEach((item) => {
          item.classList.remove("active");
        });
        this.sidebar.querySelector(".item.generate").classList.add("active");
        this.aipv.querySelectorAll(".template").forEach((template) => {
          template.classList.remove("active");
        });
        this.generateView.classList.add("for-post", "active");
        this.generateView.dataset.post = postId;
        currentPostTitle.innerHTML = postTitle;
        this.revertToOriginal.classList.add("hidden");

        // Call functions (assuming these are defined elsewhere)
        await this.getCurrentFI(postId);
        await this.checkForFIRevert(postId);
        this.setHistoryHeight();

        // Scroll to the top
        window.scrollTo({ top: 0, behavior: "smooth" });
      });
    });
  }

  async getCurrentFI(post) {
    // Set featured image
    const featuredImage = this.generateView.querySelector(".featured-img");

    // Set data object and action
    const _$data = new FormData();
    _$data.append("action", "aipv_get_current_fi");
    _$data.append("post_id", post);

    // Run fetch request
    const _$fetchRequest = await this.genericFetchRequest(_$data);

    // Update featured image
    featuredImage.style.backgroundImage = `url(${_$fetchRequest.imageUrl})`;

    // Check for missing image
    if (_$fetchRequest.text) {
      featuredImage.innerHTML = _$fetchRequest.text;
    }
  }

  async checkForFIRevert(post) {
    // Set data object and action
    const _$data = new FormData();
    _$data.append("action", "aipv_check_fi_revert");
    _$data.append("post_id", post);

    // Run fetch request
    const _$fetchRequest = await this.genericFetchRequest(_$data);

    // Update featured image
    if (_$fetchRequest) {
      this.revertToOriginal.classList.remove("hidden");
    }
  }

  loadMoreClickEvent() {
    // Set load more button click event for posts view
    this.postWrapperLoadMore
      .querySelector(".load-more-text")
      .addEventListener("click", (e) => {
        // Prevent the default action (e.g., a link click)
        e.preventDefault();

        // Add 'loading' class to the load-more text and loader elements
        this.postWrapperLoadMore
          .querySelector(".load-more-text")
          .classList.add("loading");
        this.postWrapperLoadMore
          .querySelector(".rc-loader")
          .classList.add("loading");

        // Get the current page (track current pagination)
        const currentPage =
          parseInt(this.postWrapper.dataset.currentPage, 10) || 1;
        const nextPage = currentPage + 1;

        // Call the filtering function, passing the exclude array
        this.filtering(nextPage);
      });
  }

  /***
   * GENERATE FUNCTIONS
   ***/

  resetGenerateView() {
    // Reset generate view
    this.generateView.classList.remove("for-post");
    this.generateView.dataset.post = "";
    this.generateView.querySelector(".featured-img").innerHTML = "";
    this.generateView.querySelector(".featured-img").style.backgroundImage = "";
    this.generateView.querySelector(".current-post-title").innerHTML = "";
    this.generateView.querySelectorAll(".setting input").forEach((input) => {
      input.value = "";
      const event = new Event("change");
      input.dispatchEvent(event);
    });
    this.generateView
      .querySelectorAll(".setting select")
      .forEach((select) => (select.selectedIndex = 0));
    this.generateView.querySelector(
      ".breakdown .num-images span",
    ).innerHTML = 1;
    this.generateView.querySelector(".history").style.height = "";
  }

  goBackToPostsClickEvent() {
    this.aipv.querySelector(".back-to-posts").addEventListener("click", () => {
      // Remove 'loaded' class from rendered-images
      this.renderedImages.classList.remove("loaded");

      // Clear the images wrapper content
      this.renderedImagesWrapper.innerHTML = "";

      // Remove 'for-post' class and clear 'post' data attribute
      this.generateView.classList.remove("for-post");
      this.generateView.setAttribute("data-post", "");

      // Clear the featured image and background-image
      const featuredImg = this.generateView.querySelector(".featured-img");
      featuredImg.innerHTML = "";
      featuredImg.style.backgroundImage = "";

      // Clear the current post title
      this.generateView.querySelector(".current-post-title").innerHTML = "";

      // Clear input values and trigger change event
      this.generateView.querySelectorAll(".setting input").forEach((input) => {
        input.value = "";
        const event = new Event("change");
        input.dispatchEvent(event);
      });

      // Reset the number of images and select dropdowns
      this.generateView.querySelector(
        ".breakdown .num-images span",
      ).innerHTML = 1;
      this.generateView
        .querySelectorAll(".setting select")
        .forEach((select) => {
          select.selectedIndex = 0;
          const event = new Event("change");
          select.dispatchEvent(event);
        });

      // Reset history height
      this.aipv.querySelector(".history").style.height = "";

      // Handle sidebar items' active class
      this.sidebar.querySelectorAll(".item").forEach((item) => {
        item.classList.remove("active");
      });
      this.sidebar.querySelector(".item.posts").classList.add("active");

      // Handle main content templates' active class
      this.aipv
        .querySelectorAll(".main-content .template")
        .forEach((template) => {
          template.classList.remove("active");
        });
      this.postView.classList.add("active");

      // Smooth scroll to the top
      window.scrollTo({ top: 0, behavior: "smooth" });
    });
  }

  revertToOriginalClickEvent() {
    // Set click event for revert to original button
    this.revertToOriginal.addEventListener("click", () => {
      // Get post id
      const postId = this.generateView.dataset.post;

      // revet featured image to original
      this.revertFeaturedImage(postId);
    });
  }

  async revertFeaturedImage(_postId) {
    // Set data object and action
    const _$data = new FormData();
    _$data.append("action", "aipv_revert_featured_image");
    _$data.append("post_id", _postId);

    // Run fetch request
    const _$fetchRequest = await this.genericFetchRequest(_$data);

    // Check if request successful
    if (_$fetchRequest) {
      // Set background images for the featured image and post card
      this.generateView.querySelector(
        ".current-featured .featured-img",
      ).style.backgroundImage = `url(${_$fetchRequest})`;
      this.postView.querySelector(
        `.post-card[data-post="${_postId}"] .image`,
      ).style.backgroundImage = `url(${_$fetchRequest})`;

      // Add 'hidden' class to the revert-to-original element
      this.revertToOriginal.classList.add("hidden");
    }
  }

  renderButtonClickEvent() {
    // Get validated generate view
    const validatedGenerateView = this.aipv.querySelector(
      ".template-generate.validated",
    );

    // Ensure view is validate with api key
    if (!validatedGenerateView) return;

    // Set click event for render button
    validatedGenerateView
      .querySelector(".render.btn")
      .addEventListener("click", (e) => {
        // Prevent default functionality
        e.preventDefault();

        // Set dalle image requirements
        const postId = this.generateView.classList.contains("for-post")
          ? this.generateView.dataset.post
          : false;
        const prompt = this.generateView.querySelector(".keyword-input").value;
        const num = this.generateView.querySelector(".number-input").value;
        const resolution = this.generateView.querySelector(
          ".resolution-select select",
        ).value;

        // Get dalle images
        this.getDalleImages(postId, prompt, num, resolution);
      });
  }

  async getDalleImages(_postId, _prompt, _n = false, _size = false) {
    // Clear images and start loading animation
    this.renderedImagesWrapper.innerHTML = "";
    this.renderedImages.classList.add("loaded");
    this.renderedImages.querySelector(".rc-loader").classList.add("loading");

    // Set data object and action
    const _$data = new FormData();
    _$data.append("action", "aipv_get_dalle_images");
    _$data.append("prompt", _prompt);
    _$data.append("n", _n);
    _$data.append("size", _size);
    _$data.append("post_id", _postId);

    // Run fetch request
    const _$fetchRequest = await this.genericFetchRequest(_$data);

    // Check if request successful
    if (_$fetchRequest) {
      // Stop the loading animation
      this.renderedImages
        .querySelector(".rc-loader")
        .classList.remove("loading");

      // Insert the response HTML into the images wrapper
      this.renderedImagesWrapper.innerHTML = _$fetchRequest;

      // Smooth scroll to the rendered images section
      window.scrollTo({
        top: this.renderedImages.offsetTop,
        behavior: "smooth",
      });

      // Call additional functions
      this.addNewHistoryRow();
      this.setHistoryHeight();
      this.renderedImageSetFI();
    }
  }

  async setDalleImage(_postId, _imageId) {
    // Set data object and action
    const _$data = new FormData();
    _$data.append("action", "aipv_set_dalle_image");
    _$data.append("post_id", _postId);
    _$data.append("image_id", _imageId);

    // Run fetch request
    const _$fetchRequest = await this.genericFetchRequest(_$data);

    // Check if request successful
    if (_$fetchRequest) {
      // Set featured image
      const featuredImage = this.generateView.querySelector(
        ".current-featured .featured-img",
      );

      // Set background images for the featured image and post card
      featuredImage.style.backgroundImage = `url(${_$fetchRequest})`;
      this.postView.querySelector(
        `.post-card[data-post="${_postId}"] .image`,
      ).style.backgroundImage = `url(${_$fetchRequest})`;

      // Remove 'hidden' class from the revert-to-original element
      this.revertToOriginal.classList.remove("hidden");

      // Check if there is a missing image element inside the featured image
      if (featuredImage.querySelector(".missing-image")) {
        // Remove missing image elements from both the featured image and post card
        featuredImage.querySelector(".missing-image").remove();
        this.postView
          .querySelector(
            `.post-card[data-post="${_postId}"] .image .missing-image`,
          )
          .remove();

        // Add 'hidden' class to the revert-to-original element
        this.revertToOriginal.classList.add("hidden");
      }
    }
  }

  async loadDalleHistoryRow(_historyId) {
    // Add loaded class to rendered images
    this.renderedImages.classList.add("loaded");

    // Set data object and action
    const _$data = new FormData();
    _$data.append("action", "aipv_load_dalle_history");
    _$data.append("post_id", _historyId);

    // Run fetch request
    const _$fetchRequest = await this.genericFetchRequest(_$data);

    // Check if request successful
    if (_$fetchRequest) {
      // Remove 'loading' class from the loader
      this.renderedImages
        .querySelector(".rc-loader")
        .classList.remove("loading");

      // Insert the response HTML into the images wrapper
      this.renderedImagesWrapper.innerHTML = _$fetchRequest;

      // Smooth scroll to the rendered images section
      window.scrollTo({
        top: this.renderedImages.offsetTop,
        behavior: "smooth",
      });

      // Call additional functions
      this.setHistoryHeight();
      this.renderedImageSetFI();
    }
  }

  async addNewHistoryRow() {
    // Set data object and action
    const _$data = new FormData();
    _$data.append("action", "aipv_get_history");
    _$data.append("is_ajax", 1);

    // Run fetch request
    const _$fetchRequest = await this.genericFetchRequest(_$data);

    // Check if request successful
    if (_$fetchRequest) {
      // Insert the response HTML into the history rows container
      this.aipv.querySelector(".history-rows").innerHTML = _$fetchRequest;

      // Add in load more text button
      this.historyPromptLoadMoreButtonCheck();

      // Load history images
      this.historyLoadImagesClickEvent();
    }
  }

  historyLoadImagesClickEvent() {
    // Add click event listeners to the 'load-images' buttons
    this.aipv.querySelectorAll(".history .load-images").forEach((button) => {
      button.addEventListener("click", (e) => {
        // Prevent the default action
        e.preventDefault();

        // Find the parent '.history-row' and get the 'data-history' attribute
        const historyId = button.closest(".history-row").dataset.history;

        // Call the function to load the history row
        this.loadDalleHistoryRow(historyId);
      });
    });
  }

  historyPromptLoadMoreButtonCheck() {
    // Ensure the heights are loaded
    window.requestAnimationFrame(() => {
      // Check if large prompt text present and add click event to load more button
      this.aipv
        .querySelectorAll(".history .history-row-prompt.prompt")
        .forEach((prompt) => {
          console.log(prompt);
          // Check if prompt text is larger than max width
          if (prompt.scrollHeight > 54) {
            // Create load more text button
            let loadMoreBtn = document.createElement("div");
            loadMoreBtn.classList.add("history-row-prompt", "load-more-text");
            loadMoreBtn.textContent = "Load More";

            // Add in after prompt
            prompt.insertAdjacentElement("afterend", loadMoreBtn);

            // Add click event to show rest of prompt text
            loadMoreBtn.addEventListener("click", () => {
              prompt.classList.add("opened");
              loadMoreBtn.remove();
            });
          }
        });
    });
  }

  renderedImageSetFI() {
    // Add click event listeners to the '.set-image' buttons
    this.renderedImages
      .querySelectorAll(".post-card .set-image")
      .forEach((button) => {
        button.addEventListener("click", async () => {
          // Get the post ID and image ID
          const postId = button.closest(".template-generate").dataset.post;
          const imageId = button.closest(".post-card").dataset.image;

          // Call the function to set the image
          await this.setDalleImage(postId, imageId);

          // Add the 'current' class to the clicked button
          button.classList.add("current");
        });
      });
  }

  updateCost(num, resolution) {
    // Set cost variable
    let cost;

    // Get cost from resolution
    switch (resolution) {
      case "256x256":
        cost = 0.016;
        break;
      case "512x512":
        cost = 0.018;
        break;
      case "1024x1024":
        cost = 0.02;
        break;
    }

    // Get total
    const total = (num * cost).toFixed(3);

    // Set breakdown
    const breakdown = this.aipv.querySelector(".breakdown");
    breakdown.querySelector(".num-images span").innerHTML = num;
    breakdown.querySelector(".total span").innerHTML = `$${total}`;
    breakdown.querySelector(".cost-per-img span").innerHTML = `$${cost}`;

    // Render btn update
    this.updateRenderBtn();
  }

  keywordSearchChangeEvent() {
    // Handle resolution select change
    this.aipv
      .querySelector(".keyword-input")
      .addEventListener("change", (e) => {
        // Prevent default functionality
        e.preventDefault();

        // Render btn update
        this.updateRenderBtn();
      });
  }

  numberInputChangeEvent() {
    // Handle number input change
    this.aipv.querySelector(".number-input").addEventListener("change", (e) => {
      // Prevent default functionality
      e.preventDefault();

      // Get number and resolution
      let num = e.target.value;
      const resolution = document.querySelector(
        "#aipv-admin-view .resolution-select select",
      ).value;

      // Ensure number is at least one
      if (!num) {
        num = 1;
        e.target.value = 1;
      }

      // Update cost
      this.updateCost(num, resolution);
    });
  }

  resolutionSelectChangeEvent() {
    // Handle resolution select change
    this.aipv
      .querySelector(".resolution-select select")
      .addEventListener("change", (e) => {
        // Prevent default functionality
        e.preventDefault();

        // Get number and resolution
        const resolution = e.target.value;
        const num = document.querySelector(
          "#aipv-admin-view .number-input",
        ).value;
        this.updateCost(num, resolution);
      });
  }

  signUpTextClickEvent() {
    // Set validation check
    const invalidGenerateView = this.aipv.querySelector(
      ".template-generate.not-validated",
    );

    // Skip click if valid generate view
    if (!invalidGenerateView) return;

    // Add click evnet for sign up text
    invalidGenerateView
      .querySelector(".sign-up-text div")
      .addEventListener("click", (e) => {
        // Get the value of the 'data-tab' attribute
        const tab = e.target.getAttribute("data-tab");

        // Remove 'active' class from all sidebar items
        this.sidebar.querySelectorAll(".item").forEach((item) => {
          item.classList.remove("active");
        });

        // Add 'active' class to the sidebar item that matches the tab
        this.sidebar
          .querySelector(`.item[data-tab="${tab}"]`)
          .classList.add("active");

        // Remove 'active' class from all templates
        this.aipv
          .querySelectorAll(".main-content .template")
          .forEach((template) => {
            template.classList.remove("active");
          });

        // Add 'active' class to the template that matches the tab
        document
          .querySelector(`.main-content .template[data-tab="${tab}"]`)
          .classList.add("active");

        // Smooth scroll to the top
        window.scrollTo({
          top: 0,
          behavior: "smooth",
        });

        // Modify the URL to include the new tab without reloading the page
        const url = new URL(window.location.href);
        const mainUrl = url.toString().split("&tab")[0];
        window.history.replaceState({}, "", `${mainUrl}&tab=${tab}`);
      });
  }

  setHistoryHeight() {
    const height = this.generateView.querySelector(".settings").clientHeight;
    this.aipv.querySelector(".history").style.height = height;
  }

  updateRenderBtn() {
    // Get all generate fields
    const keywordSearch = this.aipv.querySelector(".keyword-input").value;
    const numberInput = this.aipv.querySelector(".number-input").value;
    const resolution = this.aipv.querySelector(
      ".resolution-select select",
    ).value;

    // Enable or disable the button based on whether all conditions are met
    this.aipv
      .querySelector(".template-generate.validated .render.btn")
      .classList.toggle(
        "disabled",
        !(keywordSearch && numberInput && resolution),
      );
  }

  /***
   * SETTINGS FUNCTIONS
   ***/

  dalleAPIKeyInputChangeEvent() {
    // Set dalle api key input change event
    this.settingsView
      .querySelector('input[name="dalleApiKey"]')
      .addEventListener("change", async (event) => {
        // Get the value of the input field
        const apiKey = event.target.value;

        // Call the setDalleAPIKey function with the input value
        await this.setDalleAPIKey(apiKey);
      });
  }

  async setDalleAPIKey(apiKey) {
    // Set data object and action
    const _$data = new FormData();
    _$data.append("action", "aipv_set_dalle_api_key");
    _$data.append("api_key", apiKey);

    // Run fetch request
    const _$fetchRequest = await this.genericFetchRequest(_$data);

    // Remove sign up text
    const signUpText = this.generateView.querySelector(".sign-up-text");
    if (signUpText) {
      signUpText.remove();
    }
  }

  dataRetentionToggleClickEvent() {
    // Add click event for retention toggle
    this.settingsView
      .querySelector(".setting.retention .toggle-input")
      .addEventListener("click", async (e) => {
        // Get input
        const input = e.target;

        // Set data object and action
        const _$data = new FormData();
        _$data.append("action", "aipv_save_clear_data_setting");
        _$data.append("clear_data", input.checked);

        // Run fetch request
        const _$fetchRequest = await this.genericFetchRequest(_$data);
      });
  }
}
