<header>
            <h1>New Order Item</h1>
            <p class="subtitle">Please fill in the details below to add a new item.</p>
        </header>

        <form action="" method="POST">
            <!-- Brand Selection Dropdown -->
            <div class="form-group">
                <label for="brand">Choose Brand*</label>
                <select id="brand" name="brand" required aria-label="Brand Selection">
                    <option value="" selected disabled>— Select Brand —</option>
                    <option value="Dell">Dell</option>
                    <option value="HP">HP</option>
                    <option value="Lenovo">Lenovo</option>
                    <option value="Apple">Apple</option>
                    <option value="Microsoft">Microsoft</option>
                    <option value="MSI">MSI</option>
                    <option value="Asus">Asus</option>
                    <option value="Acer">Acer</option>
                    <option value="Samsung">Samsung</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <!-- Main Models Searchable Selection -->
            <div class="form-group">
                <label for="models">Main Models*</label>
                <input list="model-options" id="models" name="models" placeholder="Type or select model..." required aria-label="Models Selection">
                <datalist id="model-options">
                    <option value="Model X">
                </datalist>
            </div>

            <!-- Series Searchable Selection -->
            <div class="form-group">
                <label for="series">Series*</label>
                <input list="series-options" id="series" name="series" placeholder="Type or select series..." required aria-label="Series Selection">
                <datalist id="series-options">
                    <option value="Series 1">
                    <option value="Series 2">
                    <option value="Pro Series">
                </datalist>
            </div>


            <div class="form-group">
                <label for="description">Description*</label>
                <input list="description-options" id="description" name="description" placeholder="Type or select description..." required aria-label="Description Selection">
                <datalist id="description-options">
                    <option value="Untested">
                    <option value="Tested">
                    <option value="Parts">
                    <option value="Not Working">
                </datalist>
            </div>
            
            <!-- Quantity Selection -->
            <div class="form-group">
                <label for="qty">Quantity*</label>
                <input list="qty-options" id="qty" name="qty" placeholder="Quantity e.g. 1" required aria-label="Quantity Selection">
                <datalist id="qty-options">
                    <option value="1">
                    <option value="2">
                    <option value="3">
                    <option value="5">
                </datalist>
            </div>
            <input type="submit" value="Add Item">
        </form>

