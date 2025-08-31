<template>
  <!-- {{ item }} -->

  <template v-for="(row_item, index) in filteredFields" :key="index">
    <tr>
      <th>{{ row_item }}</th>
      <th class="text-center">:</th>
      <th class="text-trim">
        <template v-if="row_item === 'image'">
          <a :href="item[row_item] || '/avatar.png'" data-fancybox="detail-gallery"
            :data-caption="`${row_item} - Detail View`">
            <img :src="item[row_item] || '/avatar.png'" @error="handleImageError($event)"
              style="width: 120px; height: 120px; object-fit: cover" alt="image" />
          </a>
        </template>
        <template v-if="row_item === 'is_negative_marking'">
          <span v-if="item[row_item] === 1 || item[row_item] === true">Yes</span>
          <span v-else>No</span>
        </template>
        <template v-else>
          {{ trim_content(item[row_item], row_item) }}
        </template>
      </th>
    </tr>
  </template>
</template>

<script>
import setup from "../../setup";
import SelectAll from "./select_data/SelectAll.vue";
import TableRowAction from "./TableRowAction.vue";
import SelectSingle from "./select_data/SelectSingle.vue";
import { Fancybox } from "@fancyapps/ui";
import "@fancyapps/ui/dist/fancybox/fancybox.css";

export default {
  props: ["item"],
  data: () => ({
    setup,
  }),
  components: {
    SelectAll,
    TableRowAction,
    SelectSingle,
  },

  mounted() {
    this.initFancybox();
  },

  updated() {
    this.initFancybox();
  },

  beforeUnmount() {
    // Cleanup Fancybox instances
    Fancybox.destroy();
  },

  computed: {
    filteredFields() {
      return setup.select_fields.filter(field => field !== 'deleted_at');
    }
  },

  methods: {
    handleImageError(event) {
      // When image fails to load, set src to avatar.png
      event.target.src = '/avatar.png';
      // Also update the parent link href to avatar.png
      const parentLink = event.target.closest('a');
      if (parentLink) {
        parentLink.href = '/avatar.png';
      }
    },

    initFancybox() {
      // Initialize Fancybox for detail images
      Fancybox.bind('[data-fancybox="detail-gallery"]', {
        // Fancybox options for detail view
        Toolbar: {
          display: {
            left: ["infobar"],
            middle: [
              "zoomIn",
              "zoomOut",
              "toggle1to1",
              "rotateCCW",
              "rotateCW",
              "flipX",
              "flipY",
            ],
            right: ["download", "close"],
          },
        },
        Thumbs: {
          autoStart: false,
        },
        // Better for single image detail view
        wheel: "zoom",
        touch: {
          vertical: true,
          momentum: true,
        },
      });
    },

    trim_content(content, row_item = null) {
      if (typeof content == "string") {
        if (row_item == "created_at" || row_item == "updated_at") {
          return new Intl.DateTimeFormat("en-US", {
            year: "numeric",
            month: "2-digit",
            day: "2-digit",
            hour: "2-digit",
            minute: "2-digit",
            second: "2-digit",
          }).format(new Date(content));
        }

        // Strip HTML tags and decode HTML entities
        let textContent = content;

        // Remove HTML tags
        textContent = textContent.replace(/<[^>]*>/g, '');

        // Decode common HTML entities
        const entityMap = {
          '&amp;': '&',
          '&lt;': '<',
          '&gt;': '>',
          '&quot;': '"',
          '&#39;': "'",
          '&nbsp;': ' ',
          '&copy;': '©',
          '&reg;': '®',
          '&trade;': '™'
        };

        textContent = textContent.replace(/&[a-zA-Z0-9#]+;/g, (entity) => {
          return entityMap[entity] || entity;
        });

        // Clean up extra whitespace
        textContent = textContent.replace(/\s+/g, ' ').trim();

        return textContent.length > 50 ? textContent.substring(0, 50) + "..." : textContent;
      }
      if (content && typeof content === "object") {
        for (const key of Object.keys(content)) {
          if (key === "title" && content.title) {
            return content.title;
          }
          if (key === "name" && content.name) {
            return content.name;
          }
        }
      }

      return content || "";
    },
  },
};
</script>

<style scoped>
.max-w-120 {
  max-width: 120px;
}
</style>
