// External Dependencies
import React, { Component, Fragment } from 'react';

// Internal Dependencies
import './style.css';

class CustomGridModule extends Component {

  static slug = 'ccdm_grid_module';

  state = {
    terms: [],
    posts: [],
  }

  componentDidMount() {
    const baseUrl = window.wpApiSettings && window.wpApiSettings.root;
    const postType = this.props.post_type || 'post';
    const taxonomy = this.props['taxonomy_' + postType] || 'category';
    const includeCategories = this.props['include_categories_' + taxonomy] ? this.props['include_categories_' + taxonomy].split(',') : ['0'];


    this.fetchTerms(baseUrl, taxonomy, includeCategories);
    this.fetchPosts(baseUrl, postType, taxonomy, includeCategories);
  }

  fetchTerms = async (baseUrl, taxonomy, includeCategories) => {
    try {

      let apiUrl = `${baseUrl}wp/v2/${taxonomy}`;
      fetch(apiUrl)
        .then((response) => response.json())
        .then(allTerms => {
          // Filter terms based on includeCategories
          const selectedTerms = allTerms.filter((term) => includeCategories.includes(term.id.toString()));
          this.setState({ terms: selectedTerms });
        })
    } catch (error) {
      console.error(`Error fetching details for taxonomy room-category:`, error);
      // Add additional error handling as needed
    }
  };

  fetchPosts = async (baseUrl, postType, taxonomy, includeCategories) => {
    try {
      const postsNumber = this.props.posts_number;

      let apiUrl = `${baseUrl}wp/v2/${postType}?per_page=${postsNumber}`;

      if (includeCategories[0] !== '') {
        const categoryParam = includeCategories.join(',');
        apiUrl += `&${taxonomy}=${categoryParam}`;
      }

      const offsetNumber = this.props.offset_number;
      if (offsetNumber > 0) {
        apiUrl += `&offset=${offsetNumber}`;
      }

      const response = await fetch(apiUrl);
      const allPosts = await response.json();

      // Fetch image data for each post asynchronously
      const imagePromises = allPosts.map(async (post) => {
        const gallery = post.acf.gallery;
        const imageDataPromises = [];

        for (const key in gallery) {
          if (gallery.hasOwnProperty(key) && gallery[key] !== "") {
            const imageID = gallery[key];
            try {
              const imageData = await this.getImageData(imageID);
              if (imageData && !imageData.code) { // Check if imageData does not contain any error code
                imageDataPromises.push(imageData);
              } else {
                console.error(`Error fetching image data for post ID ${post.id} and image ID ${imageID}:`, imageData);
              }
            } catch (error) {
              console.error(`Error fetching image data for post ID ${post.id} and image ID ${imageID}:`, error);
            }
          }
        }

        const imageData = await Promise.all(imageDataPromises);
        return { ...post, imageData };
      });

      // Wait for all image data promises
      const postsWithImageData = await Promise.all(imagePromises);
      this.setState({ posts: postsWithImageData });

    } catch (error) {
      console.error(`Error fetching details for taxonomy room-category:`, error);
      // Add additional error handling as needed
    }
  };

  getImageData = async (imageId) => {
    try {
      const baseUrl = window.wpApiSettings && window.wpApiSettings.root;

      const apiUrl = `${baseUrl}wp/v2/media/${imageId}`;
      const response = await fetch(apiUrl);
      const imageData = await response.json();
      return imageData;
    } catch (error) {
      console.error(`Error fetching image data for ID ${imageId}:`, error);
      // Handle errors as needed
      return null;
    }
  };

  render() {
    const { terms } = this.state;
    const { posts } = this.state;

    const heading = this.props.heading;
    const viewAllText = this.props.view_all_text;
    const viewAllTextLink = this.props.view_all_text_link;

    // const postType = this.props.post_type || 'post';
    // const taxonomy = this.props['taxonomy_' + postType] || 'category';
    // const includeCategories = this.props['include_categories_' + taxonomy] ? this.props['include_categories_' + taxonomy].split(',') : ['0'];
    // const postsNumber = this.props.posts_number;
    const showImages = this.props.show_thumbnail;
    const readMore = this.props.show_more;
    const showExcerpt = this.props.show_excerpt;
    const excerptLength = this.props.excerpt_length;
    // const offsetNumber = this.props.offset_number;
    const layoutStyle = this.props.layout_style;
    const grid_count = this.props.grid_count;
    const read_more_btn_text = this.props.read_more_btn_text;

    const desc = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Lacinia quis vel eros donec ac. Euismod quis viverra nibh cras pulvinar mattis nunc sed. Aliquam vestibulum morbi blandit cursus risus. In vitae turpis massa sed elementum. Donec pretium vulputate sapien nec sagittis aliquam malesuada bibendum. Fames ac turpis egestas integer eget aliquet nibh. Suspendisse sed nisi lacus sed. Et tortor consequat id porta nibh venenatis cras. Augue interdum velit euismod in. Rutrum tellus pellentesque eu tincidunt tortor aliquam nulla facilisi. Odio tempor orci dapibus ultrices in iacu';

    let layoutClass = '';

    if (layoutStyle === '0' || layoutStyle === 'Vertical') {
      // vertical class
      layoutClass = '';
    } else if (layoutStyle === '1' || layoutStyle === 'Horizontal') {
      // horizontal class
      layoutClass = '_slider-horizontal';
    } else {
      // default vertical class
      layoutClass = '';
    }

    return (
      <Fragment>
        <div className="carousle-sections-container">
          <div className="carousel-heading-wrapper">
            {heading && <h2>{heading}</h2>}
            {viewAllText && (
              <h5>
                <a href={viewAllTextLink}>
                  {viewAllText}
                  <i className="fa-solid fa-arrow-right"></i>
                </a>
              </h5>
            )}
          </div>

          <input type="radio" name="filter" id="all" defaultChecked />
          <label htmlFor="all">{viewAllText}</label>

          {terms.map((term) => (
            <Fragment>
              <input type="radio" name="filter" id={`term-${term.id}`} />
              <label htmlFor={`term-${term.id}`}>{term.name}</label>
            </Fragment>
          ))}
          < div className={`_slider-container _slider-container-${grid_count}-col ${layoutClass} _slider-tabs`}>

            {posts.map((post) => {

              return (
                <div className="_slider category-1">

                  {showImages == 'on' &&
                    <ul className="_slider-1">
                      <li>
                        <img src='' />
                      </li>
                    </ul>
                  }

                  <div className="carousel-content-wrapper">
                    <h3>{post.title.rendered}</h3>
                    {showExcerpt === 'on' && <p>  {desc &&
                      desc.length > excerptLength
                      ? desc.substring(0, excerptLength) + '...'
                      : desc}</p>}

                    <div class="button-wrappers">
                      {readMore === 'on' && <a href={post.link} className="slider-more-btn">{read_more_btn_text}</a>}
                    </div>
                  </div>
                </div>
              );
            })}
          </div>
        </div>

      </Fragment >
    );
  }
}

export default CustomGridModule;
