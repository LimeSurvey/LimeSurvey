#!/bin/bash
echo "=== Verifying All Test Files Exist ==="
echo ""

files=(
    "tests/unit/helpers/SanitizeHelperTest.php"
    "tests/unit/LimeMailerTest.php"
    "tests/unit/services/SurveyDeactivateTest.php"
    "tests/unit/helpers/ImportHelperTest.php"
    "tests/unit/helpers/HelperLoadingTest.php"
    "tests/unit/controllers/ExpressionValidateControllerTest.php"
    "tests/unit/LSYii_ApplicationTest.php"
    "tests/unit/LSYiiValidatorsTest.php"
)

all_exist=true
for file in "${files[@]}"; do
    if [ -f "$file" ]; then
        size=$(wc -l < "$file")
        echo "✓ $file ($size lines)"
    else
        echo "✗ $file (NOT FOUND)"
        all_exist=false
    fi
done

echo ""
if [ "$all_exist" = true ]; then
    echo "✓ All test files created successfully!"
    echo ""
    echo "To run tests:"
    echo "  ./vendor/bin/phpunit tests/unit/"
else
    echo "✗ Some test files are missing"
    exit 1
fi