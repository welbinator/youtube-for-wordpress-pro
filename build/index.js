(()=>{"use strict";const e=window.wp.hooks,t=window.wp.compose,n=window.wp.blockEditor,o=window.wp.components,a=window.ReactJSXRuntime,l=(0,t.createHigherOrderComponent)((e=>t=>{const{attributes:l,setAttributes:r,name:s}=t;return"yt-for-wp/simple-youtube-feed"===s?(0,a.jsxs)(a.Fragment,{children:[(0,a.jsx)(e,{...t}),(0,a.jsx)(n.InspectorControls,{children:(0,a.jsxs)(o.PanelBody,{title:"Pro Features",children:[(0,a.jsx)(o.TextControl,{label:"YouTube Channel ID (Pro)",value:l.channelId,onChange:e=>r({channelId:e}),help:"Leave blank to use the default Channel ID from settings."}),(0,a.jsx)(o.ToggleControl,{label:"Enable User Search (Pro)",checked:l.enableSearch,onChange:e=>r({enableSearch:e})}),(0,a.jsx)(o.ToggleControl,{label:"Enable Playlist Filter (Pro)",checked:l.enablePlaylistFilter,onChange:e=>r({enablePlaylistFilter:e})})]})})]}):(0,a.jsx)(e,{...t})}),"withProControls");(0,e.addFilter)("blocks.registerBlockType","yt-for-wp-pro/add-pro-attributes",((e,t)=>("yt-for-wp/simple-youtube-feed"===t&&(e.attributes={...e.attributes,enableSearch:{type:"boolean",default:!1},enablePlaylistFilter:{type:"boolean",default:!1},channelId:{type:"string",default:""}}),e))),(0,e.addFilter)("editor.BlockEdit","yt-for-wp-pro/add-pro-controls",l),(0,e.addAction)("yt_for_wp_simple_feed_view","yt-for-wp-pro",(async(e,{channelId:t,layout:n,maxVideos:o})=>{const a=YT_FOR_WP.renderVideos,l=YT_FOR_WP.fetchVideos,r=YT_FOR_WP.apiKey,s=t||YT_FOR_WP.channelId;let i=[],c=null;await async function t(n=!1){let o=`https://www.googleapis.com/youtube/v3/playlists?part=snippet&channelId=${s}&key=${r}&maxResults=50`;c&&n&&(o+=`&pageToken=${c}`);try{const a=await fetch(o),l=await a.json();if(l.error)return void console.error("YouTube API Error:",l.error);l.items&&(i=n?[...i,...l.items]:[{id:"",snippet:{title:"All Videos"}},...l.items]),c=l.nextPageToken||null,function(){const n=e.querySelector(".youtube-filter-container");if(!n)return;const o=n.querySelector(".load-more-button");if(o&&o.remove(),c){const e=document.createElement("button");e.textContent="Load More Playlists",e.classList.add("load-more-button"),e.addEventListener("click",(()=>t(!0))),n.appendChild(e)}}()}catch(e){console.error("Error fetching playlists:",e)}}(),function(){const t=document.createElement("div");if(t.classList.add("youtube-filter-container"),i.length>0){const o=document.createElement("select");o.classList.add("youtube-playlist-dropdown"),i.forEach((({id:e,snippet:t})=>{const n=document.createElement("option");n.value=e,n.textContent=t.title,o.appendChild(n)})),o.addEventListener("change",(async()=>{const t=o.value,r=document.querySelector(".youtube-search-bar")?.value.trim()||"",s=await l(r,t);a(e,s,n)})),t.appendChild(o)}const o=document.createElement("div");o.classList.add("youtube-search-container");const r=document.createElement("input");r.type="text",r.placeholder="Search videos",r.classList.add("youtube-search-bar");const s=document.createElement("button");s.textContent="Search",s.classList.add("youtube-search-button"),r.addEventListener("keypress",(e=>{"Enter"===e.key&&(e.preventDefault(),s.click())})),s.addEventListener("click",(async()=>{const t=r.value.trim(),o=document.querySelector(".youtube-playlist-dropdown")?.value||"",s=await l(t,o);a(e,s,n)})),o.appendChild(r),o.appendChild(s),t.appendChild(o),e.prepend(t)}();const d=await l();a(e,d,n)}))})();