<script setup>
import { computed } from 'vue';

const props = defineProps({
    item: {
        type: Object,
        required: true
    },
    isActive: {
        type: Boolean,
        default: false
    },
    level: {
        type: Number,
        default: 0
    }
});

const hasChildren = computed(() => props.item.children && props.item.children.length > 0);

const dropdownClass = computed(() => {
    return props.level === 0 
        ? 'dropdown-top' 
        : 'dropdown-submenu';
});
</script>
    
<template>
    <div class="group relative">
        <!-- Regular menu item -->
        <NuxtLink 
            :to="props.item.uri" 
            :class="[
                'nav-link',
                { 'active': isActive },
                ...(item.cssClasses || [])
            ]"
            :target="item.target ? item.target : '_self'"
            :title="item.title ? item.title : ''"
        >
            {{ item.label }}
            <!-- Add dropdown indicator if there are children -->
            <span v-if="hasChildren" class="dropdown-arrow">
                {{ level === 0 ? '▼' : '▶' }}
            </span>
        </NuxtLink>

        <!-- Dropdown menu for children, if any -->
        <div v-if="hasChildren" :class="[dropdownClass, 'dropdown']">
            <div v-for="child in item.children" :key="child.id" class="dropdown-item-container">
                <!-- Recursive case: if the child has children, render another NavigationItem -->
                <template v-if="child.children && child.children.length > 0">
                    <NavigationItem 
                        :item="child" 
                        :is-active="isActive" 
                        :level="level + 1" 
                        :target="item.target ? item.target : '_self'"
                    />
                </template>
                
                <!-- Base case: if the child has no children, render a simple link -->
                <template v-else>
                    <NuxtLink 
                        :to="child.uri"
                        class="dropdown-item"
                        :target="child.target ? child.target : '_self'"
                        :class="[
                            { 'active': isActive },
                            ...(child.cssClasses || [])
                        ]"
                        :title="child.title ? child.title : ''"
                    >
                        {{ child.label }}
                    </NuxtLink>
                </template>
            </div>
        </div>
    </div>
</template>

<style scoped>
.nav-link {
    font-size: 1.125rem;
    transition: color 0.2s;
    display: inline-flex;
    align-items: center;
    text-decoration: none;
    color: #666;
    padding: 0 1rem;
}

.nav-link:hover {
    color: #222;
}

.nav-link.active {
    color: #000;
}

/* Base dropdown styles */
.dropdown {
    position: absolute;
    background-color: #eee;
    border-radius: 0.25rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    padding: 0.5rem 0;
    opacity: 0;
    visibility: hidden;
    transition: all 0.2s;
    z-index: 10;
}

/* Top-level dropdown (appears below) */
.dropdown-top {
    left: 0;
    top: 100%;
    margin-top: 0.5rem;
    width: 15rem;
}

/* Submenu dropdown (appears to the right) */
.dropdown-submenu {
    left: 100%;
    top: 0;
    width: 15rem;
    margin-left: 0.25rem;
}

.group {
    position: relative;
}

.group:hover > .dropdown {
    opacity: 1;
    visibility: visible;
}

.dropdown-item-container {
    position: relative;
}

.dropdown-item-container:hover > .dropdown-submenu {
    opacity: 1;
    visibility: visible;
}

.dropdown-item {
    display: block;
    padding: 0.5rem 1rem;
    color: #666;
    transition: background-color 0.15s;
    white-space: nowrap;
    text-decoration: none;
}

.dropdown-item:hover {
    background-color: #fff;
}

.dropdown-arrow {
    margin-left: 0.5rem;
    font-size: 0.7em;
    transition: transform 0.2s;
}

.group:hover > .nav-link .dropdown-arrow {
    transform: rotate(180deg);
}
</style>