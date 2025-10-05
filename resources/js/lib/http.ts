import axios, { AxiosError, AxiosInstance } from 'axios';
import { useToast } from '@/composables/useToast';

let client: AxiosInstance | null = null;

function getClient(): AxiosInstance {
	if (client) return client;
	client = axios.create({
		baseURL: '/',
		withCredentials: true,
		headers: { Accept: 'application/json' },
	});

	client.interceptors.request.use(async (config) => {
		// Ensure Sanctum CSRF cookie is set before first state-changing request
		const method = (config.method || 'get').toLowerCase();
		if (['post', 'put', 'patch', 'delete'].includes(method)) {
			await axios.get('/sanctum/csrf-cookie', { withCredentials: true });
		}
		return config;
	});

	client.interceptors.response.use(
		(response) => response,
		(error: AxiosError<any>) => {
			const { error: toastError } = useToast();
			const status = error.response?.status;
			if (status === 419) {
				toastError('Session expired. Please retry.');
			} else if (status && status >= 500) {
				toastError('Server error. Please try again later.');
			} else if (status && status >= 400 && status !== 422) {
				toastError(error.response?.data?.message || 'Request failed');
			}
			return Promise.reject(error);
		},
	);

	return client;
}

export async function apiGet<T>(url: string, params?: any): Promise<T> {
	const { data } = await getClient().get<T>(url, { params });
	return data;
}

export async function apiPost<T>(url: string, payload?: any): Promise<T> {
	const { data } = await getClient().post<T>(url, payload);
	return data;
}

export async function apiPut<T>(url: string, payload?: any): Promise<T> {
	const { data } = await getClient().put<T>(url, payload);
	return data;
}

export async function apiDelete<T = void>(url: string): Promise<T> {
	const { data } = await getClient().delete<T>(url);
	return data as T;
}
