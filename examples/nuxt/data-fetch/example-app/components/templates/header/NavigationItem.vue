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

<style scoped lang="scss">
@use '@/assets/scss/components/header/nav-item';
</style>