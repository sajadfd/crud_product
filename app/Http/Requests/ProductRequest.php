<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */


    public function rules(): array
    {
        switch ($this->method()) {
            case 'POST':
                return $this->store();
                break;
            case 'PUT':
                return  $this->update();
                break;
            case 'DELETE':
                return $this->destroy();
                break;
            default:
                return $this->view();
        }
    }

    public function store()
    {
        return [
            'title' => 'required|string|max:255',
            'sku' => 'required|string|max:50|unique:products',
            'slug' => 'required|string|max:255|unique:products',
            'brand_id' => 'required|integer|exists:brands,id',
            'categories' => 'required|array|min:1',
            'categories.*' => 'required|string',
            'positions' => 'required|array|min:1',
            'positions.*.price' => 'required|numeric|min:0',
            'positions.*.size' => 'required|string|max:10',
        ];
    }

    public function update()
    {
        return [
            'title' => 'required|string|max:255',
            'sku' => 'required|string|max:50|unique:products',
            'slug' => 'required|string|max:255|unique:products',
            'brand_id' => 'required|integer|exists:brands,id',
            'categories' => 'required|array|min:1',
            'categories.*' => 'required|string',
            'positions' => 'required|array|min:1',
            'positions.*.price' => 'required|numeric|min:0',
            'positions.*.size' => 'required|string|max:10',
        ];
    }
    public function destroy()
    {
        return [
            'id' => 'required|integer|exists:products,id',
        ];
    }
    public function view()
    {
        return  [
            'filter.size' => 'sometimes|required|string',
            'search' => 'required_with_all:searchBy|string',
            'searchBy' => 'required_with_all:search|string|in:id,title,sku',
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => 'Validation errors',
            'data'      => $validator->errors()
        ], 422));
    }
    /**
     * Custom message for validation
     *
     * @return array
     */
    public function messages()
    {
        return
            [
                'title.required' => 'Please provide a product title.',
                'title.max' => 'The product title must be no more than 255 characters.',
                'sku.required' => 'Please provide a product SKU.',
                'sku.max' => 'The product SKU must be no more than 50 characters.',
                'sku.unique' => 'The product SKU is already in use.',
                'slug.required' => 'Please provide a product slug.',
                'slug.max' => 'The product slug must be no more than 255 characters.',
                'slug.unique' => 'The product slug is already in use.',
                'brand_id.required' => 'Please select a product brand.',
                'brand_id.integer' => 'The product brand must be an integer.',
                'brand_id.exists' => 'The selected product brand is invalid.',
                'categories.required' => 'Please select at least one product category.',
                'categories.array' => 'The product categories must be provided as an array.',
                'categories.min' => 'Please select at least one product category.',
                'categories.*.required' => 'Please provide a valid product category.',
                'categories.*.integer' => 'The product category must be an integer.',
                'categories.*.exists' => 'The selected product category is invalid.',
                'positions.required' => 'Please provide at least one product position.',
                'positions.array' => 'The product positions must be provided as an array.',
                'positions.min' => 'Please provide at least one product position.',
                'positions.*.price.required' => 'Please provide a product position price.',
                'positions.*.price.numeric' => 'The product position price must be a number.',
                'positions.*.price.min' => 'The product position price must be at least 0.',
                'positions.*.size.required' => 'Please provide a product position size.',
                'positions.*.size.string' => 'The product position size must be a string.',
                'positions.*.size.max' => 'The product position size must be no more than 10 characters.',
            ];
    }
}
