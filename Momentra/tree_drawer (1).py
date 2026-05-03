import os

def print_tree(directory, prefix=""):
    # جلب قائمة الملفات والمجلدات
    files = sorted(os.listdir(directory))
    
    # استبعاد المجلدات المخفية مثل .git أو المجلدات المؤقتة
    files = [f for f in files if not f.startswith('.')]
    
    for i, file in enumerate(files):
        path = os.path.join(directory, file)
        is_last = (i == len(files) - 1)
        
        # اختيار الرمز المناسب (لآخر عنصر أو العناصر الوسطى)
        connector = "└── " if is_last else "├── "
        
        print(f"{prefix}{connector}{file}")
        
        # إذا كان العنصر مجلداً، نقوم بالدخول إليه (Recursion)
        if os.path.isdir(path):
            extension = "    " if is_last else "│   "
            print_tree(path, prefix + extension)

# تشغيل الكود على المجلد الحالي
if __name__ == "__main__":
    project_path = "D:\elgam3a\second year\second term\Web programming II\project\Momentra"  # يمكنك تغيير النقطة لمسار مشروعك
    print(f"Project Root: {os.path.abspath(project_path)}")
    print_tree(project_path)