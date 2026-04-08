import { ref, onMounted } from 'vue';
import axios from 'axios';

export function useApiData<T = any>(endpoint: string) {
    const data = ref<T | null>(null);
    const isLoading = ref(true);
    const error = ref<string | null>(null);

    const fetchData = async () => {
        isLoading.value = true;
        error.value = null;
        try {
            const response = await axios.get(endpoint);
            data.value = response.data.data !== undefined ? response.data.data : response.data;
        } catch (err: any) {
            error.value = err.response?.data?.message || err.message || 'Ocurrió un error al cargar la información.';
        } finally {
            isLoading.value = false;
        }
    };

    onMounted(() => {
        if (endpoint) {
            fetchData();
        }
    });

    return {
        data,
        isLoading,
        error,
        refetch: fetchData
    };
}
