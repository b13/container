/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
import DocumentService from"@typo3/core/document-service.js";import DataHandler from"@typo3/backend/ajax-data-handler.js";import Icons from"@typo3/backend/icons.js";import RegularEvent from"@typo3/core/event/regular-event.js";import{DataTransferTypes}from"@typo3/backend/enum/data-transfer-types.js";import BroadcastService from"@typo3/backend/broadcast-service.js";import{BroadcastMessage}from"@typo3/backend/broadcast-message.js";var Identifiers,Classes;!function(e){e.content=".t3js-page-ce",e.draggableContent=".t3js-page-ce-sortable",e.draggableContentHandle=".t3js-page-ce-draghandle",e.dropZone=".t3js-page-ce-dropzone-available",e.column=".t3js-page-column",e.addContent=".t3js-page-new-ce"}(Identifiers||(Identifiers={})),function(e){e.validDropZoneClass="active",e.dropPossibleHoverClass="t3-page-ce-dropzone-possible"}(Classes||(Classes={}));class DragDrop{constructor(){DocumentService.ready().then((()=>{this.initialize()}))}initialize(){new RegularEvent("mousedown",((e,t)=>{const a=e.target.closest("a,img");if(null!==a&&t.contains(a))return;t.closest(Identifiers.content).querySelector(Identifiers.draggableContentHandle).draggable=!0})).delegateTo(document,Identifiers.draggableContentHandle),new RegularEvent("dragstart",this.onDragStart.bind(this)).delegateTo(document,Identifiers.draggableContentHandle),new RegularEvent("dragenter",this.onDragEnter.bind(this)).delegateTo(document,Identifiers.draggableContentHandle),new RegularEvent("dragend",this.onDragEnd.bind(this)).delegateTo(document,Identifiers.draggableContentHandle),new RegularEvent("dragenter",((e,t)=>{t.classList.add(Classes.dropPossibleHoverClass),e.dataTransfer.dropEffect=e.ctrlKey?"copy":"move"})).delegateTo(document,Identifiers.dropZone),new RegularEvent("dragover",(e=>{e.preventDefault(),e.dataTransfer.dropEffect=e.ctrlKey?"copy":"move"})).delegateTo(document,Identifiers.dropZone),new RegularEvent("dragleave",((e,t)=>{e.preventDefault(),t.classList.remove(Classes.dropPossibleHoverClass)})).delegateTo(document,Identifiers.dropZone),new RegularEvent("drop",this.onDrop.bind(this),{capture:!0,passive:!0}).delegateTo(document,Identifiers.dropZone),new RegularEvent("typo3:page-layout-drag-drop:elementChanged",this.onBroadcastElementChanged.bind(this)).bindTo(top.document)}onDragEnter(e){e.preventDefault(),e.dataTransfer.dropEffect=e.ctrlKey?"copy":"move",this.showDropZones()}onDragStart(e,t){const a=t.closest(Identifiers.content);e.dataTransfer.setData(DataTransferTypes.content,JSON.stringify({pid:this.getCurrentPageId(),uid:parseInt(a.dataset.uid,10),language:parseInt(a.dataset.languageUid,10),content:a.outerHTML}));const n=this.getDragTooltipMetadataFromContentElement(a);e.dataTransfer.setData(DataTransferTypes.dragTooltip,JSON.stringify(n)),e.dataTransfer.effectAllowed="copyMove",e.dataTransfer.dropEffect="copy",window.setTimeout((()=>{const e=document.createElement("span");e.classList.add("t3js-draggable-copy-message","badge","badge-secondary"),e.textContent=TYPO3.lang["dragdrop.copy.message"],a.append(e)}),0),a.closest(Identifiers.column).classList.remove("active"),a.querySelector(Identifiers.dropZone).hidden=!0}onDragEnd(e,t){const a=t.closest(Identifiers.content);a.draggable=!1,a.closest(Identifiers.column).classList.add("active"),a.querySelector(Identifiers.dropZone).hidden=!1,a.querySelector(".t3js-draggable-copy-message").remove(),this.hideDropZones()}onDrop(e,t){let a;if(t.classList.remove(Classes.dropPossibleHoverClass),!e.dataTransfer.types.includes(DataTransferTypes.content))return;const n=this.getColumnPositionForElement(t),o=this.getTxContainerParentPositionForElement(t),r=JSON.parse(e.dataTransfer.getData(DataTransferTypes.content));if(a=document.querySelector(`${Identifiers.content}[data-uid="${r.uid}"]`),a||(a=document.createRange().createContextualFragment(r.content).firstElementChild),"number"==typeof r.uid&&r.uid>0){const s={},d=t.closest(Identifiers.content).dataset.uid;let i;i=void 0===d?parseInt(t.closest("[data-page]").dataset.page,10):0-parseInt(d,10);let l=r.language;-1!==l&&(l=parseInt(t.closest("[data-language-uid]").dataset.languageUid,10));let c=0,g=0;0!==i&&(c=n,g=o);const p=e.ctrlKey||t.classList.contains("t3js-paste-copy"),u=p?"copy":"move";s.cmd={tt_content:{[r.uid]:{[u]:{action:"paste",target:i,update:{colPos:c,sys_language_uid:l,tx_container_parent:g}}}}},this.ajaxAction(s,p).then((()=>{t.parentElement.classList.contains(Identifiers.content.substring(1))?t.closest(Identifiers.content).after(a):t.closest(Identifiers.dropZone).after(a),this.broadcast("elementChanged",{pid:r.pid,uid:r.uid,targetPid:this.getCurrentPageId(),action:p?"copy":"move"});const e=document.querySelector(`.t3-page-column-lang-name[data-language-uid="${l}"]`);if(null===e)return;const n=e.dataset.flagIdentifier,o=e.dataset.languageTitle;Icons.getIcon(n,Icons.sizes.small).then((e=>{const t=a.querySelector(".t3js-flag");t.title=o,t.innerHTML=e}))}))}}onBroadcastElementChanged(e){e.detail.payload.pid===this.getCurrentPageId()&&e.detail.payload.targetPid!==e.detail.payload.pid&&"move"===e.detail.payload.action&&document.querySelector(`${Identifiers.content}[data-uid="${e.detail.payload.uid}"]`).remove()}ajaxAction(e,t){const a=Object.keys(e.cmd).shift(),n=parseInt(Object.keys(e.cmd[a]).shift(),10),o={component:"dragdrop",action:t?"copy":"move",table:a,uid:n},r=document.querySelector(".t3-grid-container");return DataHandler.process(e,o).then((e=>{if(e.hasErrors)throw e.messages;(t||"1"===r?.dataset.defaultLanguageBinding)&&self.location.reload()}))}getColumnPositionForElement(e){const t=e.closest("[data-colpos]");return null!==t&&void 0!==t.dataset.colpos&&parseInt(t.dataset.colpos,10)}getTxContainerParentPositionForElement(e){const t=e.closest("[data-colpos]");return null!==t&&void 0!==t.dataset.txContainerParent?parseInt(t.dataset.txContainerParent,10):0}getDragTooltipMetadataFromContentElement(e){let t,a;const n=[],o=e.querySelector(".t3-page-ce-header-title").innerText,r=e.querySelector(".element-preview");r&&(t=r.innerText,t.length>80&&(t=t.substring(0,80)+"..."));const s=e.querySelector(".t3js-icon");s&&(a=s.dataset.identifier);const d=e.querySelectorAll(".preview-thumbnails-element-image img");return d.length>0&&d.forEach((e=>{n.push({src:e.src,height:e.height,width:e.width})})),{tooltipIconIdentifier:a,tooltipLabel:o,tooltipDescription:t,thumbnails:n}}getCurrentPageId(){return parseInt(document.querySelector("[data-page]").dataset.page,10)}broadcast(e,t){BroadcastService.post(new BroadcastMessage("page-layout-drag-drop",e,t||{}))}showDropZones(){document.querySelectorAll(Identifiers.dropZone).forEach((e=>{const t=e.parentElement.querySelector(Identifiers.addContent);null!==t&&(t.hidden=!0,e.classList.add(Classes.validDropZoneClass))}))}hideDropZones(){document.querySelectorAll(`${Identifiers.dropZone}.${Classes.validDropZoneClass}`).forEach((e=>{const t=e.parentElement.querySelector(Identifiers.addContent);null!==t&&(t.hidden=!1),e.classList.remove(Classes.validDropZoneClass)}))}}export default new DragDrop;