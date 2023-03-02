import React, { useState } from "react";
import Authenticated from "@/Layouts/AuthenticatedLayout.jsx";
// import { Head, useForm, usePage, Link } from '../../../node_modules/@inertiajs/react';
import { Head, useForm, usePage, Link } from "@inertiajs/react";

export default function Dashboard() {
    // const { files } = usePage().props

    // const { data, setData, errors, post, progress } = useForm({
    //     title: "",
    //     file: [null],
    // });

    // function handleSubmit(e) {
    //     e.preventDefault();
    //     //post(route("file.register"));
    //     console.log(e.target.file)
    //     setData("title", "")
    //     setData("file", null)
    // }

    const [files, setFiles] = useState('');
    //state for checking file size
    const [fileSize, setFileSize] = useState(true);
    // for file upload progress message
    const [fileUploadProgress, setFileUploadProgress] = useState(false);
    //for displaying response message
    const [fileUploadResponse, setFileUploadResponse] = useState(null);
    //base end point url
    const FILE_UPLOAD_BASE_ENDPOINT = "http://localhost:8000/";

    const uploadFileHandler = (event) => {
        setFiles(event.target.files);
       };

      const fileSubmitHandler = (event) => {
       event.preventDefault();
       setFileSize(true);
       setFileUploadProgress(true);
       setFileUploadResponse(null);

        const formData = new FormData();

        for (let i = 0; i < files.length; i++) {
            if (files[i].size > 10240000){
                setFileSize(false);
                setFileUploadProgress(false);
                setFileUploadResponse(null);
                return;
            }

            formData.append(`files`, files[i])
        }

        const requestOptions = {
            method: 'POST',
            body: formData
        };
        fetch(FILE_UPLOAD_BASE_ENDPOINT+'file-upload', requestOptions)
            .then(async response => {
                const isJson = response.headers.get('content-type')?.includes('application/json');
                const data = isJson && await response.json();

                // check for error response
                if (!response.ok) {
                    // get error message
                    const error = (data && data.message) || response.status;
                    setFileUploadResponse(data.message);
                    return Promise.reject(error);
                }

               console.log(data.message);
               setFileUploadResponse(data.message);
            })
            .catch(error => {
                console.error('Error while uploading file!', error);
            });
        setFileUploadProgress(false);
      };

    return(

      <form onSubmit={fileSubmitHandler}>
         <input type="file"  multiple onChange={uploadFileHandler}/>
         <button type='submit'>Upload</button>
         {!fileSize && <p style={{color:'red'}}>File size exceeded!!</p>}
         {fileUploadProgress && <p style={{color:'red'}}>Uploading File(s)</p>}
        {fileUploadResponse!=null && <p style={{color:'green'}}>{fileUploadResponse}</p>}
      </form>

    );

    // return (
    //     <Authenticated
    //         auth={props.auth}
    //         errors={props.errors}
    //         header={
    //             <h2 className="font-semibold text-xl text-gray-800 leading-tight">
    //                 Laravel React JS File Upload Example - ItSolutionStuff.com
    //             </h2>
    //         }
    //     >
    //         <Head title="Posts" />

    //         <div className="py-12">
    //             <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
    //                 <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    //                     <div className="p-6 bg-white border-b border-gray-200">
    //                         <form
    //                             name="createForm"
    //                             onSubmit={fileSubmitHandler}
    //                         >
    //                             <div className="flex flex-col">
    //                                 <div className="mb-4">
    //                                     <label className="">Title</label>
    //                                     <input
    //                                         type="text"
    //                                         className="w-full px-4 py-2"
    //                                         label="Title"
    //                                         name="title"
    //                                         value={data.title}
    //                                         onChange={(e) =>
    //                                             setData("title", e.target.value)
    //                                         }
    //                                     />
    //                                     <span className="text-red-600">
    //                                         {errors.title}
    //                                     </span>
    //                                 </div>
    //                                 <div className="mb-0">
    //                                     <label className="">File</label>
    //                                     <input
    //                                         type="file"
    //                                         className="w-full px-4 py-2"
    //                                         label="File"
    //                                         name="file"
    //                                         data-multiple-caption="{count} Archivos Seleccionados"
    //                                         multiple
    //                                         onChange={uploadFileHandler}
    //                                     />
    //                                     <span className="text-red-600"></span>
    //                                 </div>
    //                             </div>

    //                             {progress && (
    //                                 <div className="w-full bg-gray-200 rounded-full">
    //                                     <div
    //                                         className="bg-blue-600 text-xs font-medium text-blue-100 text-center p-0.5 leading-none rounded-full"
    //                                         width={progress.percentage}
    //                                     >
    //                                         {" "}
    //                                         {progress.percentage}%
    //                                     </div>
    //                                 </div>
    //                             )}

    //                             <div className="mt-4">
    //                                 <button
    //                                     type="submit"
    //                                     className="px-6 py-2 font-bold text-white bg-green-500 rounded"
    //                                 >
    //                                     Save
    //                                 </button>
    //                             </div>
    //                         </form>

    //                         <br />

    //                         <h1>Uploaded File List:</h1>
    //                         <table className="table-fixed w-full">
    //                             <thead>
    //                                 <tr className="bg-gray-100">
    //                                     <th className="px-4 py-2 w-20">No.</th>
    //                                     <th className="px-4 py-2">Title</th>
    //                                     <th className="px-4 py-2">Image</th>
    //                                 </tr>
    //                             </thead>
    //                             <tbody>
    //                                 {files.map(({ id, title, name }) => (
    //                                     <tr>
    //                                         <td className="border px-4 py-2">
    //                                             {id}
    //                                         </td>
    //                                         <td className="border px-4 py-2">
    //                                             {title}
    //                                         </td>
    //                                         <td className="border px-4 py-2">
    //                                             <img src={name} width="200px" />
    //                                         </td>
    //                                     </tr>
    //                                 ))}

    //                                 {files.length === 0 && (
    //                                     <tr>
    //                                         <td
    //                                             className="px-6 py-4 border-t"
    //                                             colSpan="4"
    //                                         >
    //                                             No contacts found.
    //                                         </td>
    //                                     </tr>
    //                                 )}
    //                             </tbody>
    //                         </table>
    //                     </div>
    //                 </div>
    //             </div>
    //         </div>
    //     </Authenticated>
    // );
}
