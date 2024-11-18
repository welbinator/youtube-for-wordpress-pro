(()=>{"use strict";const e=window.wp.hooks,t=window.wp.compose,o=window.wp.blockEditor,n=window.wp.components,r=window.ReactJSXRuntime,a=(0,t.createHigherOrderComponent)((e=>t=>{const{attributes:a,setAttributes:l,name:s}=t;return"yt-for-wp/simple-youtube-feed"===s?(0,r.jsxs)(r.Fragment,{children:[(0,r.jsx)(e,{...t}),(0,r.jsx)(o.InspectorControls,{children:(0,r.jsxs)(n.PanelBody,{title:"Pro Features",children:[(0,r.jsx)(n.ToggleControl,{label:"Enable User Search (Pro)",checked:a.enableSearch,onChange:e=>l({enableSearch:e})}),(0,r.jsx)(n.ToggleControl,{label:"Enable Playlist Filter (Pro)",checked:a.enablePlaylistFilter,onChange:e=>l({enablePlaylistFilter:e})})]})})]}):(0,r.jsx)(e,{...t})}),"withProControls");(0,e.addFilter)("blocks.registerBlockType","yt-for-wp-pro/add-pro-attributes",((e,t)=>("yt-for-wp/simple-youtube-feed"===t&&(e.attributes={...e.attributes,enableSearch:{type:"boolean",default:!1},enablePlaylistFilter:{type:"boolean",default:!1}}),e))),(0,e.addFilter)("editor.BlockEdit","yt-for-wp-pro/add-pro-controls",a),(0,e.addAction)("yt_for_wp_simple_feed_view","yt-for-wp-pro",(async(e,{channelId:t,layout:o,maxVideos:n})=>{const r="https://www.googleapis.com/youtube/v3",a=YT_FOR_WP.apiKey;let l=[],s=null;async function i(e="",o=""){let l=`${r}/search?part=snippet&type=video&channelId=${t}&maxResults=${n}&key=${a}`;o&&(l=`${r}/playlistItems?part=snippet&maxResults=${n}&playlistId=${o}&key=${a}`),e&&(l+=`&q=${encodeURIComponent(e)}`);try{const e=await fetch(l),t=await e.json();return t.error?(console.error("YouTube API Error:",t.error),[]):t.items||[]}catch(e){return console.error("Error fetching videos:",e),[]}}await async function o(n=!1){let i=`${r}/playlists?part=snippet&channelId=${t}&key=${a}&maxResults=50`;s&&n&&(i+=`&pageToken=${s}`);try{const t=await fetch(i),r=await t.json();if(r.error)return void console.error("YouTube API Error:",r.error);r.items&&(l=n?[...l,...r.items]:[{id:"",snippet:{title:"All Videos"}},...r.items]),s=r.nextPageToken||null,function(){const t=e.querySelector(".youtube-filter-container");if(!t)return;const n=t.querySelector(".load-more-button");if(n&&n.remove(),s){const e=document.createElement("button");e.textContent="Load More Playlists",e.classList.add("load-more-button"),e.addEventListener("click",(()=>o(!0))),t.appendChild(e)}}()}catch(e){console.error("Error fetching playlists:",e)}}(),function(){const t=document.createElement("div");if(t.classList.add("youtube-filter-container"),l.length>0){const n=document.createElement("select");n.classList.add("youtube-playlist-dropdown"),l.forEach((({id:e,snippet:t})=>{const o=document.createElement("option");o.value=e,o.textContent=t.title,n.appendChild(o)})),n.addEventListener("change",(async()=>{const t=n.value,r=document.querySelector(".youtube-search-bar")?.value.trim()||"",a=await i(r,t);renderVideos(e,a,o)})),t.appendChild(n)}const n=document.createElement("div");n.classList.add("youtube-search-container");const r=document.createElement("input");r.type="text",r.placeholder="Search videos",r.classList.add("youtube-search-bar");const a=document.createElement("button");a.textContent="Search",a.classList.add("youtube-search-button"),r.addEventListener("keypress",(e=>{"Enter"===e.key&&(e.preventDefault(),a.click())})),a.addEventListener("click",(async()=>{const t=r.value.trim(),n=document.querySelector(".youtube-playlist-dropdown")?.value||"",a=await i(t,n);renderVideos(e,a,o)})),n.appendChild(r),n.appendChild(a),t.appendChild(n),e.appendChild(t)}()}))})();