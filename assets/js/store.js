document.addEventListener('DOMContentLoaded', () => {
    
    // Vehicle Compatibility Chained Dropdown Logic
    const makeSelect = document.getElementById('filter_make');
    const modelSelect = document.getElementById('filter_model');
    
    if (makeSelect && modelSelect) {
        // Function to fetch models based on make_id
        const fetchModels = async (makeId, selectedModelId = '') => {
            if (!makeId) {
                modelSelect.innerHTML = '<option value="">All Models</option>';
                modelSelect.disabled = true;
                return;
            }
            
            try {
                // We'll create a simple API endpoint in api/get-models.php
                const response = await fetch(`/api/get-models.php?make_id=${makeId}`);
                if (!response.ok) throw new Error('Network response was not ok');
                
                const models = await response.json();
                
                let options = '<option value="">All Models</option>';
                models.forEach(model => {
                    const isSelected = selectedModelId == model.model_id ? 'selected' : '';
                    options += `<option value="${model.model_id}" ${isSelected}>${model.model_name}</option>`;
                });
                
                modelSelect.innerHTML = options;
                modelSelect.disabled = false;
                
            } catch (error) {
                console.error('Error fetching models:', error);
                modelSelect.innerHTML = '<option value="">Error loading models</option>';
                modelSelect.disabled = true;
            }
        };

        // Listen for make change
        makeSelect.addEventListener('change', (e) => {
            const makeId = e.target.value;
            fetchModels(makeId);
        });

        // Initial load if make is already selected (e.g. after form submission)
        if (makeSelect.value) {
            // Get the current selected model if it exists in data attribute
            const currentModel = modelSelect.getAttribute('data-selected') || '';
            fetchModels(makeSelect.value, currentModel);
        }
    }
    
    // Quantity increment/decrement on product page
    const qtyInput = document.getElementById('qty');
    const btnMinus = document.getElementById('qty-minus');
    const btnPlus = document.getElementById('qty-plus');
    
    if (qtyInput && btnMinus && btnPlus) {
        const max = parseInt(qtyInput.getAttribute('max')) || 999;
        const min = parseInt(qtyInput.getAttribute('min')) || 1;
        
        btnMinus.addEventListener('click', () => {
            let val = parseInt(qtyInput.value) || min;
            if (val > min) {
                qtyInput.value = val - 1;
            }
        });
        
        btnPlus.addEventListener('click', () => {
            let val = parseInt(qtyInput.value) || min;
            if (val < max) {
                qtyInput.value = val + 1;
            }
        });
        
        // Prevent manual input of values outside range
        qtyInput.addEventListener('change', () => {
            let val = parseInt(qtyInput.value);
            if (isNaN(val) || val < min) qtyInput.value = min;
            if (val > max) qtyInput.value = max;
        });
    }
});
