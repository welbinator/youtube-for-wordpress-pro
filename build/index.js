(()=>{"use strict";const e=window.wp.hooks,t=window.wp.compose,n=window.wp.blockEditor,o=window.wp.components,a=window.ReactJSXRuntime,l=(0,t.createHigherOrderComponent)((e=>t=>{const{attributes:l,setAttributes:r,name:s}=t;return"yt-for-wp/simple-youtube-feed"===s?(0,a.jsxs)(a.Fragment,{children:[(0,a.jsx)(e,{...t}),(0,a.jsx)(n.InspectorControls,{children:(0,a.jsxs)(o.PanelBody,{title:"Pro Features",children:[(0,a.jsx)(o.TextControl,{label:"YouTube Channel ID (Pro)",value:l.channelId,onChange:e=>r({channelId:e}),help:"Leave blank to use the default Channel ID from settings."}),(0,a.jsx)(o.ToggleControl,{label:"Enable User Search (Pro)",checked:l.enableSearch,onChange:e=>r({enableSearch:e})}),(0,a.jsx)(o.ToggleControl,{label:"Enable Playlist Filter (Pro)",checked:l.enablePlaylistFilter,onChange:e=>r({enablePlaylistFilter:e})})]})})]}):(0,a.jsx)(e,{...t})}),"withProControls");(0,e.addFilter)("blocks.registerBlockType","yt-for-wp-pro/add-pro-attributes",((e,t)=>("yt-for-wp/simple-youtube-feed"===t&&(e.attributes={...e.attributes,enableSearch:{type:"boolean",default:!1},enablePlaylistFilter:{type:"boolean",default:!1},channelId:{type:"string",default:""}}),e))),(0,e.addFilter)("editor.BlockEdit","yt-for-wp-pro/add-pro-controls",l),(0,e.addAction)("yt_for_wp_simple_feed_view","yt-for-wp-pro",(async(e,t)=>{const{channelId:n,layout:o,maxVideos:a,enableSearch:l="true"===e.dataset.enableSearch,enablePlaylistFilter:r="true"===e.dataset.enablePlaylistFilter}=t;console.log("Pro Attributes:",{enableSearch:l,enablePlaylistFilter:r});const s=YT_FOR_WP.renderVideos,i=YT_FOR_WP.fetchVideos,c=YT_FOR_WP.apiKey,d=n||YT_FOR_WP.channelId;let u=[],p=null;await async function t(n=!1){let o=`https://www.googleapis.com/youtube/v3/playlists?part=snippet&channelId=${d}&key=${c}&maxResults=50`;p&&n&&(o+=`&pageToken=${p}`);try{const a=await fetch(o),l=await a.json();if(l.error)return void console.error("YouTube API Error:",l.error);l.items&&(u=n?[...u,...l.items]:[{id:"",snippet:{title:"All Videos"}},...l.items]),p=l.nextPageToken||null,function(){const n=e.querySelector(".youtube-filter-container");if(!n)return;const o=n.querySelector(".load-more-button");if(o&&o.remove(),p){const e=document.createElement("button");e.textContent="Load More Playlists",e.classList.add("load-more-button"),e.addEventListener("click",(()=>t(!0))),n.appendChild(e)}}()}catch(e){console.error("Error fetching playlists:",e)}}(),function(){const t=document.createElement("div");if(t.classList.add("youtube-filter-container"),r&&u.length>0){const n=document.createElement("select");n.classList.add("youtube-playlist-dropdown"),u.forEach((({id:e,snippet:t})=>{const o=document.createElement("option");o.value=e,o.textContent=t.title,n.appendChild(o)})),n.addEventListener("change",(async()=>{const t=n.value,a=document.querySelector(".youtube-search-bar")?.value.trim()||"",l=await i(a,t);s(e,l,o)})),t.appendChild(n)}if(l){const n=document.createElement("div");n.classList.add("youtube-search-container");const a=document.createElement("input");a.type="text",a.placeholder="Search videos",a.classList.add("youtube-search-bar");const l=document.createElement("button");l.textContent="Search",l.classList.add("youtube-search-button"),a.addEventListener("keypress",(e=>{"Enter"===e.key&&(e.preventDefault(),l.click())})),l.addEventListener("click",(async()=>{const t=a.value.trim(),n=document.querySelector(".youtube-playlist-dropdown")?.value||"",l=await i(t,n);s(e,l,o)})),n.appendChild(a),n.appendChild(l),t.appendChild(n)}t.children.length>0&&e.prepend(t)}();const h=await i();s(e,h,o)}))})();