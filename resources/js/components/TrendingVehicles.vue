<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

// TODO: implement
//   - fetch GET /api/vehicles/trending on mount
//   - auto-refresh every 30s
//   - render the list (make, model, year, view count)
//   - loading + error states
//   - clean up the interval on unmount

const vehicles = ref([]);
const loading = ref(false);
const error = ref(null);
let refreshInterval = null;

async function load() {
    // TODO

    //SOLUTION
    try {
        error.value = null;

        const response = await fetch('/api/vehicles/trending');

        if (!response.ok) {
            throw new Error('Failed to load trending vehicles');
        }

        vehicles.value = await response.json();
    } catch (err) {
        error.value = err.message || 'Something went wrong';
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    load();
    // TODO: set up polling

    //SOLUTION
    refreshInterval = setInterval(() => {
        load();
    }, 30000);
});

onUnmounted(() => {
    // TODO: clear polling


    //SOLUTION
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
});
</script>

<template>
    <section class="trending-vehicles">
        <h2>Trending vehicles:</h2>
        <br>
        <!-- TODO: loading state, error state, list of vehicles -->

        <!--SOLUTION-->
        <p v-if="loading">Loading trending vehicles...</p>

        <p v-else-if="error" class="error">
            {{ error }}
        </p>

        <ol v-else-if="vehicles.length">
            <li v-for="vehicle in vehicles" :key="vehicle.id">
                <strong>
                    {{ vehicle.make }} {{ vehicle.model }}<br> 
                </strong>

                <p>
                    Year: {{ vehicle.year }}
                </p>
                <p>
                    Price:${{ vehicle.price }}
                </p>
                <p>
                    Views: {{ vehicle.views_count }} views
                </p>
                <br>
            </li>
        </ol>

        <p v-else>No trending vehicles yet.</p>
    </section>
</template>
