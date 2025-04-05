                                <thead>
                                    <tr>
                                        <th class="text-center">رقم</th>
                                        <th class="text-center">عنوان الاختبار</th>
                                        <th class="text-center">المقرر</th>
                                        <th class="text-center">المجموعة</th>
                                        <th class="text-center">المدة (دقيقة)</th>
                                        <th class="text-center">الحالة</th>
                                        <th class="text-center">المدرس</th>
                                        <th class="text-center">الإجراءات</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach($exams as $key => $exam)
                                        <tr>
                                            <td class="text-center">{{ $key+1 }}</td>
                                            <td class="text-center">{{ $exam->title }}</td>
                                            <td class="text-center">{{ $exam->course->name }}</td>
                                            <td class="text-center">{{ $exam->group->name }}</td>
                                            <td class="text-center">{{ $exam->duration }}</td>
                                            <td class="text-center">
                                                <div class="dropdown">
                                                    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton{{ $exam->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        الإجراءات
                                                    </button>
                                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $exam->id }}">
                                                        <a class="dropdown-item" href="{{ route('admin.exams.show', $exam->id) }}">عرض</a>
                                                        <a class="dropdown-item" href="{{ route('admin.exams.edit', $exam->id) }}">تعديل</a>
                                                        <a class="dropdown-item" href="{{ route('admin.exams.report.detail', $exam->id) }}">تقرير تفصيلي</a>
                                                        <form action="{{ route('admin.exams.destroy', $exam->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا الاختبار بشكل نهائي؟')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger">حذف</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach 